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


/**
 * THIS FILE IS HERE TO PROVIDE A TEMPLATE FOR LOGIN METHODS.
 * 
 * WHEN CREATING A NEW LOGIN METHOD, YOU MUST MATCH THESE FUNCTIONS.  IT IS
 * RECOMMENDED THAT YOU COPY THIS FILE AND THEN EDIT IT.
 */

// Define the authentication information.
$info = "To login, enter your username and password in the fields provided below.";
define("AUTHENTICATION_INFO", $info);

/**
 * Perform the authentication of a user using their username and password.
 * 
 * @param string $username The username of the member.
 * @param string $pass The password of the member.
 * @return boolean True if successful (granted access), false otherwise.
 */
function auth_login($username, $pass){
    // query the database.
    $query = mysql_query("SELECT *
                          FROM `".SQL_PREFIX."members`
                          WHERE `username` = '" . mysql_real_escape_string($username) . "'
                            AND `password` = '" . mysql_real_escape_string($pass) . "'
                          LIMIT 1");
    
    // Return result.
    return (mysql_num_rows($query) > 0);
}

/**
 * Get the memberID of this account.
 * 
 * @param string $username The Username of the member.
 * @return integer The memberID.
 */
function auth_getID($username){
    // Query the database.
    $query = mysql_query("SELECT *
                          FROM `".SQL_PREFIX."members`
                          WHERE `username` = '" . mysql_real_escape_string($username) . "'");
    
    // If no rows, return 0.
    if(mysql_num_rows($query) < 1){
        return 0;
    }
    
    // Get the row
    $row = mysql_fetch_object($query);
    
    // Return the ID.
    return $row->memberID;
}

/**
 * Get the name you'd expect this user to use when connecting to the server.
 * 
 * @param int $memberID The ID of the member.
 * @return string The expected name of the user.
 */
function auth_getName($memberID){
    // Query
    $query = mysql_query("SELECT *
                          FROM `".SQL_PREFIX."members`
                          WHERE `memberID` = '" . mysql_real_escape_string($memberID) . "'");
    
    // If there are no rows, return null - non existant.
    if(mysql_num_rows($query) < 1){
        return NULL;
    }
    
    // Get the row.
    $row = mysql_fetch_object($query);
    
    // Return the status
    return $row->name;
}

/**
 * Check the status of a user (active, inactive, banned, etc).
 * 
 * @param integer|string $memberID The ID of the member.
 * @return int 0 - doesn't exist, 1 - active, 2 - inactive, 3 - banned/suspended.
 */
function auth_checkStatus($memberID){
    // Query the database.
    $query = mysql_query("SELECT *
                          FROM `".SQL_PREFIX."members`
                          WHERE `memberID` = '" . mysql_real_escape_string($memberID) . "'");
    
    // If there are no rows, return 0 - non existant.
    if(mysql_num_rows($query) < 1){
        return 0;
    }
    
    // Get the row.
    $row = mysql_fetch_object($query);
    
    // Return the status
    return $row->status;
}

?>