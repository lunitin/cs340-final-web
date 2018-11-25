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
  // already authenticated
  if (isset($_SESSION["user"]) ) {
    return password_verify(TOKEN_SALT . $_SESSION["user"]["user_id"], $_SESSION["user"]["token"]);
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

  if (isset($_SESSION["msg"]) && count($_SESSION["msg"] > 0) ) {
    $template = file_get_contents("template/alert.html");

    if (isset($_SESSION["msg"]["danger"])) {
      foreach($_SESSION["msg"]["danger"] as $k => $v) {
        $html .= str_replace(array('TYPE', 'MSG'), array('alert-danger', $v), $template);
      }
    }
    if (isset($_SESSION["msg"]["success"])) {
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
                                  WHERE user_id=:user_id');
  $sql->bindParam(':user_id', $_SESSION["user"]["user_id"]);
  $sql->execute();
  $rows = $sql->fetch();

  return $rows;
}


/*********************************************************************
** Function: fetch_categories
** Description: Fetch any categories for this user
** Return: array() of category tuples
*********************************************************************/
function fetch_categories() {

  try {
    $sql = $GLOBALS["db"]->prepare('SELECT *
                                    FROM categories
                                    WHERE user_id=:user_id');
    $sql->bindParam(':user_id', $_SESSION["user"]["user_id"]);
    $sql->execute();
    $rows = $sql->fetch();

    $_SESSION["msg"]["success"][] = "Added Bucket ". $_POST["bucket_name"];


  } catch (\PDOException $e) {
    $_SESSION["msg"]["danger"][] = "PDO Exception on Insert";
      return false;
  }


  return $rows;
}



/*********************************************************************
** Function: fetch_actions() {
** Description: Fetch any messages in the session
** Return: HTML containing rendered message content
*********************************************************************/
function fetch_actions() {
  // Cheap check for Login
  if (isset($_SESSION["user"]["user_id"])) {
    return file_get_contents("template/actions.html");
  }

}

?>
