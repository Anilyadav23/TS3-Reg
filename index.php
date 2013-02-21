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

define("SUPPRESS_AUTH", true);
require_once "./includes/setup.php";

// If the form has been submitted, process it.
if (isset($_POST["login"], $_POST["username"], $_POST["password"])) {
    $username = $_POST["username"];
    $password = $_POST['password'];
    $memberID = auth_getID($username); // Attempt to get the member ID early.

    // If their details are invalid, error.
    if (!auth_login($username, $password) || $memberID < 1) {
        header("Location: index_form.php?error=login");
        exit();
    }
    
    // Check status - we won't allow banned members to login, they've been naughty.
    if(auth_checkStatus($memberID) == 3){
        header("Location: index_form.php?error=netBanned");
        exit();
    }

    // Check if banned on the TS server.
    $query = mysql_query("SELECT *
                          FROM `".SQL_PREFIX."banned`
                          WHERE (`banCriteria` = 'user=" . mysql_real_escape_string($username) . "')
                             OR (`banCriteria` = 'id=" . mysql_real_escape_string($memberID) . "')");
    if (mysql_num_rows($query) > 0) {
        header("Location: index_form.php?error=banned");
        exit();
    }
    
    // If we've got this far, they've succeeded! Let them login.
    $_SESSION["memberID"] = $memberID;
}

if(isset($_SESSION["memberID"])){
    header("Location: members_form.php");
    exit();
} else {
    header("Location: index_form.php");
    exit();
}

?>