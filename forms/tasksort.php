<?php
/*********************************************************************
** Program Filename: updatesorty.php
** Author: Casey Dinsmore
** Date: 2018-11-26
** Description: Update the sort order of tasks in a category and
**              support movement between categories.
********************************************************************/
include_once("../config.php");

$resp = array();

// Check for authentication token
if (verify_login()) {

    if (update_sort()) {
      $code = '201';
    } else {
      $code = '501';
    }

} else {
  $code = 500;
  $_SESSION["msg"]["danger"][] = "Not authorized.";
}


$resp['code'] = $code;
$resp['messages'] = $_SESSION["msg"];

$_SESSION["msg"] = array();

print json_encode($resp);


/*********************************************************************
** Function: update_sort
** Description: Update the sort and category of a collection of tasks
** Return: Boolean - result of the update statement(s)
*********************************************************************/
function update_sort() {

  try {

    $sql = $GLOBALS["db"]->prepare('UPDATE tasks
                                    SET
                                    sort_weight = :sort_weight,
                                    category_id = :category_id
                                    WHERE task_id=:task_id
                                    AND user_id= :user_id');

    foreach($_POST["sort"] as $cat => $task) {

      foreach($task as $task_id => $sort_weight) {

        $sql->bindParam(':user_id', $_SESSION["user"]["user_id"]);
        $sql->bindParam(':category_id', $cat);
        $sql->bindParam(':task_id', $task_id);
        $sql->bindParam(':sort_weight', $sort_weight);
        $sql->execute();
      }

    }
    return true;

  } catch (\PDOException $e) {
    $_SESSION["msg"]["danger"][] = "ERROR: PDO Exception on insert.";
      return false;
  }
}


/*********************************************************************
** Function: update_bucket
** Description: Update an existing bucket
** Return: Boolean - result of the update query
*********************************************************************/
function update_bucket() {

  try {

    $sql = $GLOBALS["db"]->prepare('UPDATE buckets
                          SET
                          bucket_name = :bucket_name,
                          bucket_title = :bucket_title
                          WHERE
                          bucket_id = :bucket_id
                          AND user_id = :user_id');

    $sql->bindParam(':bucket_id', $_POST["bucket_id"]);
    $sql->bindParam(':user_id', $_SESSION["user"]["user_id"]);
    $sql->bindParam(':bucket_name', $_POST["bucket_name"]);
    $sql->bindParam(':bucket_title', $_POST["bucket_title"]);
    $sql->execute();

    if ($sql->rowCount() > 0) {
        return true;
    } else {
        return false;
    }


  } catch (\PDOException $e) {
    $_SESSION["msg"]["danger"][] = "ERROR: PDO Exception on update.";
    return false;
  }
}


/*********************************************************************
** Function: load_bucket
** Description: Load a single bucket from the db
** Return: array - result of the select query
*********************************************************************/
function load_bucket($bucket_id) {

  try {

    $sql = $GLOBALS["db"]->prepare('SELECT *
                                    FROM buckets
                                    WHERE user_id=:user_id
                                    AND bucket_id=:bucket_id');

    $sql->bindParam(':user_id', $_SESSION["user"]["user_id"]);
    $sql->bindParam(':bucket_id', $bucket_id);
    $sql->execute();

    $row = $sql->fetch();
    return $row;

  } catch (\PDOException $e) {
    $_SESSION["msg"]["danger"][] = "ERROR: PDO Exception on select.";
    return false;
  }
}

?>
