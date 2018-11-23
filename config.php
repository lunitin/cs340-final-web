<?php
/*********************************************************************
** Program Filename: config.php
** Author: Casey Dinsmore
** Date: 2018-11-21
** Description: Configuration details
********************************************************************/
// Show errors for testing - Disable for prod
ini_set('display_errors', 'on');

define('DB_NAME', 'todo');
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'todo');
define('DB_DSN', 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4');

// Password is kept outside docroot and will set DB_PASS
include_once('../config/todo-config.php');

// Initiate PDO handler
$GLOBALS["db"] = new PDO(DB_DSN, DB_USER, DB_PASS,
                     array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                           PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                           PDO::ATTR_EMULATE_PREPARES => false) );

?>
