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

require_once "./includes/setup.php";
$error = "";

// Do they want to delete a registration?
if (isset($_POST["register"], $_POST["uniqueID"])) {
    $uniqueID = $_POST["uniqueID"];
    
    // Check this user is allowed more registrations.
    $query = mysql_query("SELECT *
                          FROM `".SQL_PREFIX."registrations`
                          WHERE `memberID` '" . mysql_real_escape_string($memberID) . "'
                            AND `deleted` = 0");
    if(mysql_num_rows($query) >= TS_MAX_ID){
        header("Location: members_form.php?error=max_reg");
        exit();
    }

    // Check that this uniqueID hasn't already been registered.
    $query = mysql_query("SELECT *
                          FROM `".SQL_PREFIX."registrations`
                          WHERE `uniqueID` = '" . mysql_real_escape_string($uniqueID) . "'
                              AND `deleted` = '0'");
    if(mysql_num_rows($query) > 0){
        header("Location: members_form.php?error=already_reg");
        exit();
    }
    
    // Check the make sure this uniqueID hasn't been banned.
    $query = mysql_query("SELECT *
                          FROM `".SQL_PREFIX."banned`
                          WHERE `banCriteria` = 'unique=" . mysql_real_escape_string($uniqueID) . "'");
    if (mysql_num_rows($query) > 0) {
        header("Location: members_form.php?error=banned");
        exit();
    }
    
    // Get unique token for them.
    try {
        $virt = TeamSpeak3::factory(TS_QUERY);
        $token = $virt->privilegeKeyCreate(TeamSpeak3::TOKEN_SERVERGROUP, TS_ME_GROUP, 0, "Member ID: " . $memberID . " new registration.");
        $token = $token->toString();
    } catch (Exception $e) {
        header("Location: members_form.php?error=new_flood");
        exit();
    }
    
    // Register the token in the database.
    mysql_query("INSERT INTO `registrations`
                 (`memberID`, `expectedName`, `uniqueID`, `token`, `registeredIP`, `registeredTimestamp`)
                 VALUES
                 ('".$memberID."', '" . auth_getName($memberID) . "', '" . mysql_real_escape_string($uniqueID) . "', '" . $token . "', '".$_SERVER["REMOTE_ADDR"]."', '" . date("Y-m-d H:i:s") . "')");

    header("Location: members_form.php?newSuccess=" . urlencode($token));
    exit();
}

// Send to the members page.
header("Location: members_form.php");

?>