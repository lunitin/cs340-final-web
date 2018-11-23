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
include_once('HTML/QuickForm2.php');

ob_start();
session_start();

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


/*********************************************************************
** Function: verify_login
** Description: Check if the browser has a valid session cookie
** Return: Boolean - status of token check
*********************************************************************/
function verify_login() {

  // If the hash in the cookie matches a calculated one, they have
  // already authenticated
  if (isset($_SESSION["user"]) ) {
    return password_verify(TOKEN_SALT . $_SESSION["user"]["user_id"], $_SESSION["user"]["token"]);
  }
  return false;
}

/*********************************************************************
** Function: fetch_messages
** Description: Fetch any messages in the session
** Return: HTML containing rendered message content
*********************************************************************/
function fetch_messages() {

  $html = "";

  if (isset($_SESSION["msg"]) && count($_SESSION["msg"] > 0) ) {
    $template = file_get_contents("template/alert.html");

    if (isset($_SESSION["msg"]["error"])) {
      foreach($_SESSION["msg"]["error"] as $k => $v) {
        $html .= str_replace(array('TYPE', 'MSG'), array('alert-danger', $v), $template);
      }
    }
    if (isset($_SESSION["msg"]["success"])) {
      foreach($_SESSION["msg"]["success"] as $k => $v) {
        $html .= str_replace(array('TYPE', 'MSG'), array('alert-success', $v), $template);
      }
    }
    // clear out the messages for next page change
    $_SESSION["msg"] = array();
  }
  return $html;
}



/*********************************************************************
** Function: fetch_buckets
** Description: Fetch any buckets and inject them into the template
** Return: HTML containing rendered bucket container
*********************************************************************/
function fetch_buckets() {

  return $html;
}


/*********************************************************************
** Function: fetch_actions() {
** Description: Fetch any messages in the session
** Return: HTML containing rendered message content
*********************************************************************/
function fetch_actions() {
  // Cheap check for Login
  if (isset($_SESSION["user"]["user_id"])) {
    return file_get_contents("template/actions.html");
  }

}

?>
