<?php
include_once("../config.php");


$buckets = fetch_buckets();
$code = 200;

// Create a JSON object to send back to the frontend
ob_start();
print $form->render($r);
$html = ob_get_clean();

$resp['code'] = $code;
$resp['html'] = $html;
$resp['messages'] = $_SESSION["msg"];

$_SESSION["msg"] = array();

print json_encode($resp);



?>
