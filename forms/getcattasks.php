<?php
/*********************************************************************
** Program Filename: getcattasks.php
** Author: Casey Dinsmore
** Date: 2018-11-22
** Description: Provide Categories and Tasks via JSON to the frontend
********************************************************************/
include_once("../config.php");
include_once("../common.php");

$resp = array();

// Check for authentication token
if (verify_login()) {

  $bucket_id = (int) $_GET["bucket_id"];

  // Fetch all categories and tasks in this bucket for this user_id
  $resp['categories'] = fetch_categories($bucket_id);
  $resp['tasks'] = fetch_tasks($bucket_id);
  $code = 200;

} else {
  $code = 500;
  $_SESSION["msg"]["danger"][] = "Not authorized.";
}

$resp['code'] = $code;
$resp['messages'] = $_SESSION["msg"];

$_SESSION["msg"] = array();

print json_encode($resp);

?>
