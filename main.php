<?php
/*********************************************************************
** Program Filename: index.php
** Author: Casey Dinsmore
** Date: 2018-11-21
** Description: Primary controller
********************************************************************/
include_once('config.php');
include_once('forms/login.php');
include_once('forms/signup.php');

$page = (int) $_GET["page"];

// Check for authentication token
// proceed to main page
if (verify_login() && $page != 2) {
//  $_SESSION["msg"]["success"][] = "Logged in";
  $page = 4;
} //else {
  //$_SESSION["msg"]["error"][] = "Not logged in";
//}
// Load the main HTML Template
$template = file_get_contents('template/index.html');

// Setup the array of placeholders in the template
$data = array(
  'STR_BUCKETS'=> '','STR_ACTIONS' => '' ,'STR_TITLE' => '','STR_BODY' => '','STR_MSG'=> ''
);


// Page processing loop
switch($page) {

  case '4':
    $data['STR_TITLE'] = "Todo List";
    $data['STR_BODY'] = 'hi';
    break;
  case '3':
    $data['STR_TITLE'] = "Create an Account";
    $data['STR_BODY'] = form_signup();
    break;
  case '2': // Logout
    $_SESSION = array();
    $_SESSION["msg"]["success"] = "You have been logged out";
  case '1':
  default:
    $data['STR_TITLE'] = "Log in";
    $data['STR_BODY'] = form_login();

}

// Replace all placeholders and dump the template
$data['STR_MSG'] = fetch_messages();
$data['STR_ACTIONS'] = fetch_actions();
$data['STR_BUCKETS'] = '';
print str_replace(array_keys($data), $data, $template);


print "<PRE>" . print_r($_SESSION, true)  . "</PRE>";
?>
