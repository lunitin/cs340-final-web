<?php
/*********************************************************************
** Program Filename: delete.php
** Author: Casey Dinsmore
** Date: 2018-11-22
** Description: Provide Deletion handling for multiple events
********************************************************************/
include_once("../config.php");
include_once("../common.php");

$resp = array();

// Check for authentication token
if (verify_login()) {

  $code = delete_item();

} else {
  $code = 500;
  $_SESSION["msg"]["danger"][] = "Not authorized.";
}

$resp['code'] = $code;
$resp['messages'] = $_SESSION["msg"];
$_SESSION["msg"] = array();

print json_encode($resp);


/*********************************************************************
** Function: delete_item
** Description: Delete the specified item by ID
** Return: Boolean - result of the deletion
*********************************************************************/
function delete_item() {

  $code = 201;
  // Look up table and primary key names
  switch($_GET["type"]) {
    case 'bucket':
      $sql = $GLOBALS["db"]->prepare('DELETE FROM buckets WHERE user_id=:user_id AND bucket_id=:id');
      $sql->bindParam(':id', $_GET["id"]);
      break;
    case 'category':
      $sql = $GLOBALS["db"]->prepare('DELETE FROM categories WHERE user_id=:user_id AND category_id=:id');
      $sql->bindParam(':id', $_GET["id"]);
      break;
    case 'task':
      $sql = $GLOBALS["db"]->prepare('DELETE FROM tasks WHERE user_id=:user_id AND task_id=:id');
      $sql->bindParam(':id', $_GET["id"]);
      break;
    case 'user':
      $sql = $GLOBALS["db"]->prepare('DELETE FROM user WHERE user_id=:user_id');
      $code = 206;
      break;
    default:
      $_SESSION["msg"]["danger"][] = "Invalid type passed.";
      return 500;
  }


  try {
    $sql->bindParam(':user_id', $_SESSION["user"]["user_id"]);
    $sql->execute();

    if ($sql->rowCount() > 0) {
      // Deleted account so reset auth token
      if ($code == 206) {
        $_SESSION["user"] = array();
      }
        return $code;
    } else {
        return 500;
    }


  } catch (\PDOException $e) {
    $_SESSION["msg"]["danger"][] = "ERROR: PDO Exception on delete";
    return 500;
  }


}

?>
