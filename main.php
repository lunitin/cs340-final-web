<?php
/*********************************************************************
** Program Filename: index.php
** Author: Casey Dinsmore
** Date: 2018-11-09
** Description: Primary controller
********************************************************************/
include_once('config.php');
include_once('common.php');
include_once('forms/login.php');
include_once('forms/signup.php');

// Get a page id from .htaccess Rewrite Rule
$page = (int) $_GET["page"];

// Check for authentication token
// if valid proceed to main page
if (verify_login() && $page != 2) {
  $page = 4;
}

// Load the main HTML Template
$template = file_get_contents('template/index.html');

// Setup the array of placeholders in the template
$data = array(
  'STR_TITLE' => '','STR_BODY' => '','STR_MSG'=> ''
);

// Page processing loop
switch($page) {
  case '4':
    $data['STR_TITLE'] = '';
    $data['STR_BODY'] = '';
    $template = file_get_contents('template/todo.html');
    break;
  case '3':
    $data['STR_TITLE'] = "Create an Account";
    $data['STR_BODY'] = form_signup();
    break;
  case '2':
    // Logout - clear user token
    $_SESSION = array();
    $_SESSION['msg'] = array();
    $_SESSION["msg"]["success"][] = "You have been logged out";
    header('Location: /');
    exit;
  case '1':
  default:
    $data['STR_TITLE'] = "Log in";
    $data['STR_BODY'] = form_login();
}

// Replace all placeholders and dump the template
$data['STR_MSG'] = fetch_messages();
print str_replace(array_keys($data), $data, $template);

?>
