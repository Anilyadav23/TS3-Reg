<?php

// Define the authentication information.
$info = "To login, please user your VATSIM ID as your username and your NETWORK PASSWORD as your password.";
define("AUTHENTICATION_INFO", $info);

// Auto tools information
define("AUTO_TOOLS_USER", ""); // Insert your auto tools username here.
define("AUTO_TOOLS_PASS", ""); // Insert your auto tools password here.
define("AUTO_TOOLS_URL", ""); // Insert the base URL for auto tools here (http://....min/) REMEMBER TO INCLUDE THE TRAILING SLASH!

/**
 * Perform the authentication of a user using their ID and password.
 * 
 * @param integer|string $cid The ID of the member.
 * @param string $pass The password of the member.
 * @return boolean True if successful (granted access), false otherwise.
 */
function auth_login($cid, $pass){
    // Get the response from auto tools.
    $url = AUTO_TOOLS_URL . "pwordcheck.php?authid=".AUTO_TOOLS_USER."&authpassword=".AUTO_TOOLS_PASS."&id=".$cid."&password=".$pass;
    $result = file($url);
    
    var_dump($result);
    die($url);

    // If the result isn't an array, return false
    if(!$result || !is_array($result)){
        return false;
    }

    // If the result is 1, return true.
    if(trim($result[0]) == '1'){
        return true;
    }

    // Return false as default
    return false;
}

/**
 * Get the memberID of this account.
 * 
 * @param string $username The Username of the member.
 * @return integer The memberID.
 */
function auth_getID($username){
    return $username; // Username IS the CID.
}

/**
 * Get the name you'd expect this user to use when connecting to the server.
 * 
 * @param int $cid The ID of the member.
 * @return string The expected name of the user.
 */
function auth_getName($cid){
    // Query
    $xmlFeed = simplexml_load_file("http://cert.vatsim.net/vatsimnet/idstatusint.php?cid=" . $cid);

    if (!$xmlFeed->user->name_first || !$xmlFeed->user->name_last) {
        return NULL;
    }
    
    return $xmlFeed->user->name_first . " " . $xmlFeed->user->name_last;
}
/**
 * Check the status of a user (active, inactive, banned, etc).
 * 
 * @param integer|string $cid The ID of the member.
 * @return int 0 - doesn't exist, 1 - active, 2 - inactive, 3 - banned/suspended.
 */
function auth_checkStatus($cid){
    // Check if network banned.
    $xmlFeed = simplexml_load_file("http://cert.vatsim.net/vatsimnet/idstatusint.php?cid=" . $cid);

    if ($xmlFeed->user->rating < 1) {
        return 3;
    }
    
    return 1;
}

?>