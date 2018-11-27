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
** Description: Fetch any categories for this user
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
** Function: fetch_categories
** Description: Fetch any categories for this user
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
