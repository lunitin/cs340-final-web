<?php
include_once("../config.php");
include_once("../common.php");

$code = 200;

// Create a JSON object to send back to the frontend
$resp['code'] = $code;
$resp['buckets'] = fetch_buckets();
$resp['messages'] = $_SESSION["msg"];

$_SESSION["msg"] = array();

print json_encode($resp);



?>
