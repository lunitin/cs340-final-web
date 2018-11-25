<?php
include_once("../config.php");


$buckets = fetch_buckets();
$code = 200;

// Create a JSON object to send back to the frontend
$resp['code'] = $code;
$resp['html'] = $html;
$resp['messages'] = $_SESSION["msg"];

$_SESSION["msg"] = array();

print json_encode($resp);



?>
