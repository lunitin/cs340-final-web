<?php
/*********************************************************************
** Program Filename: common.php
** Author: Casey Dinsmore
** Date: 2018-11-21
** Description: Common functions required by multiple files
********************************************************************/



/*********************************************************************
** Function: verify_login
** Description: Check if the browser has a valid session cookie
** Return: Boolean - status of token check
*********************************************************************/
function verify_login() {

  // If the hash in the cookie matches a calculated one, they have
  // already authenticated and the user_id and email in the cookie
  // have not been altered
  if (isset($_SESSION["user"]["user_id"]) && isset($_SESSION["user"]["email"]) ) {
    return password_verify(TOKEN_SALT . $_SESSION["user"]["user_id"] . $_SESSION["user"]["email"] , $_SESSION["user"]["token"]);
  }
  return false;
}


/*********************************************************************
** Function: fetch_messages
** Description: Fetch any messages in the session
** Return: HTML containing rendered message content
*********************************************************************/
function fetch_messages() {

  $html = "";

  if (isset($_SESSION["msg"]) && count($_SESSION["msg"]) > 0 ) {
    $template = file_get_contents("template/alert.html");

    if (isset($_SESSION["msg"]["danger"]) && count($_SESSION["msg"]["danger"]) > 0 ) {
      foreach($_SESSION["msg"]["danger"] as $k => $v) {
        $html .= str_replace(array('TYPE', 'MSG'), array('alert-danger', $v), $template);
      }
    }
    if (isset($_SESSION["msg"]["success"]) && count($_SESSION["msg"]["success"]) > 0) {
      foreach($_SESSION["msg"]["success"] as $k => $v) {
        $html .= str_replace(array('TYPE', 'MSG'), array('alert-success', $v), $template);
      }
    }
    // clear out the messages for next page change
    $_SESSION["msg"] = array();
  }
  return $html;
}



/*********************************************************************
** Function: fetch_buckets
** Description: Fetch any buckets for this user
** Return: array() of bucket tuples
*********************************************************************/
function fetch_buckets() {

  $sql = $GLOBALS["db"]->prepare('SELECT *
                                  FROM buckets
                                  WHERE user_id=:user_id
                                  ORDER by sort_weight');
  $sql->bindParam(':user_id', $_SESSION["user"]["user_id"]);
  $sql->execute();
  $rows = $sql->fetchAll();

  return $rows;
}


/*********************************************************************
** Function: fetch_categories
** Description: Fetch any categories for this user in a bucket
** Parameters: bucket_id int
** Return: array() of category tuples
*********************************************************************/
function fetch_categories($bucket_id) {

  try {
    $sql = $GLOBALS["db"]->prepare('SELECT *
                                    FROM categories
                                    WHERE user_id=:user_id
                                    AND bucket_id=:bucket_id
                                    ORDER by sort_weight');

    $sql->bindParam(':user_id', $_SESSION["user"]["user_id"]);
    $sql->bindParam(':bucket_id', $bucket_id);
    $sql->execute();
    $rows = $sql->fetchAll();

    return $rows;

  } catch (\PDOException $e) {
    $_SESSION["msg"]["danger"][] = "PDO Exception on cat select";
      return false;
  }
}

/*********************************************************************
** Function: fetch_tasks
** Description: Fetch any categories for this user in a bucket
** Parameters: bucket_id int
** Return: array() of category tuples
*********************************************************************/
function fetch_tasks($bucket_id) {

  try {
    $sql = $GLOBALS["db"]->prepare('SELECT *
                                    FROM tasks
                                    WHERE bucket_id=:bucket_id
                                    ORDER by sort_weight');

    $sql->bindParam(':bucket_id', $bucket_id);
    $sql->execute();
    $rows = $sql->fetchAll();

    return $rows;

  } catch (\PDOException $e) {
    $_SESSION["msg"]["danger"][] = "PDO Exception on task select";
      return false;
  }
}


/*********************************************************************
** Function: fetch_bootstrap_renderer();
** Description: Create a Bootstrap compataible renderer for QuickForm2
** Return: HTML_QuickForm2_Renderer object
*********************************************************************/
function fetch_bootstrap_renderer() {

  // Create a custom renderer for BootStrap
  $r = HTML_QuickForm2_Renderer::factory('callback');
  $r->setCallbackForClass('HTML_QuickForm2_Element', function($renderer, $element) {
      $error = $element->getError();
      if ($error) {
          $html[] = '<div class="clearfix form-group">';
          $element->addClass('is-invalid');
      } else {
          $html[] = '<div class="clearfix form-group">';
      }
      $html[] = $renderer->renderLabel($element->addClass('red'));
      $html[] = '<div class="input">'.$element;
      if ($error) {
          $html[] = '<span class="invalid-feedback">'.$error.'</span>';
      } else {
          $label = $element->getLabel();
        if (is_array($label) && !empty($label[1])) {
              $html[] = '<span class=" valid-feedback">'.$label[1].'</span>';
        }
      }
      $html[] = '</div></div>';
      return implode('', $html);
  });
  return $r;
}
?>
