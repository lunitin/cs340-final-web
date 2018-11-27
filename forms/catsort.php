<?php
/*********************************************************************
** Program Filename: catsort.php
** Author: Casey Dinsmore
** Date: 2018-11-26
** Description: Update the sort order of categories in a bucket.
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
** Description: Update the sort weight of categories
** Return: Boolean - result of the update statement(s)
*********************************************************************/
function update_sort() {

  try {

    $sql = $GLOBALS["db"]->prepare('UPDATE categories
                                    SET
                                    sort_weight = :sort_weight
                                    WHERE category_id=:category_id
                                    AND user_id= :user_id');

    foreach($_POST["sort"] as $cat => $sort_weight) {

        $sql->bindParam(':user_id', $_SESSION["user"]["user_id"]);
        $sql->bindParam(':category_id', $cat);
        $sql->bindParam(':sort_weight', $sort_weight);
        $sql->execute();

    }
    return true;

  } catch (\PDOException $e) {
    $_SESSION["msg"]["danger"][] = "ERROR: PDO Exception on insert.";
      return false;
  }
}


?>
