<?php

/**
 * @author Anthony Lawrence <freelancer@anthonylawrence.me.uk>
 * @copyright Copyright (c) 2011, Anthony Lawrence
 *
 * Released as is with no guarantees and all that usual stuff.
 *
 * This copyright must remain intact at all times.
 * @version 1.5
 */

/******************************************************************************/
/*************** DO NOT EDIT UNLESS YOU KNOW WHAT YOU ARE DOING ***************/
/******************************************************************************/

session_start();
require_once "config.php";

// To counteract any web servers that are behind a proxy, we need to get the current IP in a different way.
// Modify the IP, because we're behind a proxy
if (array_key_exists("HTTP_X_FORWARDED_FOR", $_SERVER)) {
    $ip = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
    if (is_array($ip)) {
        $ip = trim($ip[count($ip) - 1]);
    }
    $_SERVER["REMOTE_ADDR"] = $ip;
}

// Construct the TS server query URL.
$tsQuery = "serverquery://" . TS_USER . ":" . TS_PASS;
$tsQuery.= "@" . TS_HOST . ":" . TS_QPORT . "/";
$tsQuery.= "?server_port=" . TS_PORT;
define("TS_QUERY", $tsQuery);

// Set the date/time.
$datetime = date("Y-m-d H:i:s");

// Connect to the MySQL server.
mysql_pconnect(SQL_HOST, SQL_USER, SQL_PASS) or die("MYSQL DATABASE FAILURE!  PLEASE CHECK YOUR CONNECTION SETTINGS OR CONTACT SUPPORT.");
mysql_select_db(SQL_DB);

// We NEED to include the TS3-SDK and the auth module.
try {
    require_once "./TeamSpeak3/TeamSpeak3.php";
    require_once "./includes/auth/" . AUTHENTICATION_FILE;
} catch(Exception $e){
    die("COULN'T INCLUDE NECESSARY FILES! PLEASE CONTACT SUPPORT.");
}

// Include all function files - these aren't mission critical.
$files = scandir("./includes/library/");
foreach($files as $f){
    if($f == "." || $f == ".."){ continue; }
    include_once "./includes/library/" . $f;
}

// If the SUPPRESS_AUTH flag isn't set, we need to be checking for an authenticated user!
$memberID = 0;
$permissions = array("userUI" => 0);
if(!defined("SUPPRESS_AUTH")){
    // Check for no user.
    if(!isset($_SESSION["memberID"])){
        header("Location: index.php");
        exit();
    }
    
    // Check for change in user status.
    if(auth_checkStatus($_SESSION["memberID"]) != 1){
        unset($_SESSION["memberID"]);
        header("Location: index.php");
        exit();
    }
    
    // Set the memberID.
    $memberID = $_SESSION["memberID"];
    
    // Get the member permissions from the admin list.
    $permissions["userUI"] = 1;
    $query = mysql_query("SELECT *
                          FROM `".SQL_PREFIX."permissions`
                          WHERE `memberID` = '".$memberID."'");
    while($p = mysql_fetch_object($query)){
        $permissions[$p->permission] = $p->value;
        $permissions[$p->permission . "_extra"] = $p->extra;
    }
}

?>