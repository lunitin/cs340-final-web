<?php
/*********************************************************************
** Program Filename: getbuckets.php
** Author: Casey Dinsmore
** Date: 2018-11-22
** Description: Provide Buckets for this user via JSON to the frontend
********************************************************************/
include_once("../config.php");
include_once("../common.php");

$resp = array();

// Check for authentication token
if (verify_login()) {

  // Fetch all the buckets for this user_id
  $resp['buckets'] = fetch_buckets();
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
