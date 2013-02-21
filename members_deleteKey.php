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
if (isset($_GET['registrationDelete'])) {
    $action = (isset($_GET['action']) ? $_GET['action'] : 'token');
    $actionInfo = (isset($_GET['actionInfo']) ? $_GET['actionInfo'] : '');
    $registrationID = $_GET['registrationDelete'];

    // Check they own this registration
    $query = mysql_query("SELECT *
                          FROM `".SQL_PREFIX."registrations`
                          WHERE `registrationID` = '" . mysql_real_escape_string($registrationID) . "'
                              AND `memberID` = '" . mysql_real_escape_string($memberID) . "'
                              AND `deleted` = '0'");

    
    // Get the information - if it exists.
    if(mysql_num_rows($query) > 0){
        $registration = mysql_fetch_object($query);

        // Try and delete the privlidge key from the TS server.
        $virt = TeamSpeak3::factory(TS_QUERY);
        $virt->privilegeKeyDelete($registration->token);

        // Now try and a) kick the client from the server and b) delete from the TS database.
        try {
            $client = $virt->clientGetByUid($registration->uniqueID);
            $client->kick(TeamSpeak3::KICK_SERVER, "Registration removed.");
            $client->deleteDb();
        } catch (Exception $e) {
            header("Location: members_form.php?error=del_flood&errorType=del");
        }

        // Update the registrations table.
        mysql_query("UPDATE `registrations`
                     SET `deleted` = '1',
                         `deletedReason` = 'user_request_web',
                         `deletedIP` = '".$_SERVER["REMOTE_ADDR"]."',
                         `deletedTimestamp` = '".date("Y-m-d H:i:s")."'
                     WHERE `registrationID` = '".mysql_real_escape_string($registrationID)."'
                       AND `memberID` = '".mysql_real_escape_string($memberID)."'
                       AND `deleted` = '0'");
        
        // Success!
        header("Location: members_form.php?delSuccess");
        exit();
    } else {
        header("Location: members_form.php?error=invalid_client&errorType=del");
        exit();
    }
}

// Send to the members page.
header("Location: members_form.php");

?>