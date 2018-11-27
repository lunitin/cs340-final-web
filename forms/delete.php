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

  if(delete_item()) {
    $code = 201;
  } else {
    $code = 500;
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
** Function: delete_item
** Description: Delete the specified item by ID
** Return: Boolean - result of the deletion
*********************************************************************/
function delete_item() {

  // Look up table and primary key names
  switch($_GET["type"]) {
    case 'bucket':
      $stmt = 'DELETE FROM buckets WHERE user_id=:user_id AND bucket_id=:id';
      break;
    case 'category':
      $stmt = 'DELETE FROM categories WHERE user_id=:user_id AND category_id=:id';
      break;
    case 'task':
      $stmt = 'DELETE FROM tasks WHERE user_id=:user_id AND task_id=:id';
      break;
    default:
      $_SESSION["msg"]["danger"][] = "Invalid type passed.";
      return false;
  }


  try {
    $sql = $GLOBALS["db"]->prepare($stmt);
    $sql->bindParam(':user_id', $_SESSION["user"]["user_id"]);
    $sql->bindParam(':id', $_GET["id"]);
    $sql->execute();

    if ($sql->rowCount() > 0) {
        return true;
    } else {
        return false;
    }


  } catch (\PDOException $e) {
    $_SESSION["msg"]["danger"][] = "ERROR: PDO Exception on delete". $e->getMessage();
    return false;
  }


}

?>
