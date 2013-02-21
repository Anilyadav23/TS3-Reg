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
/*********** SETTINGS FOR TS3 BOT - MODIFY THESE TO MEET YOUR NEEDS ***********/
/******************************************************************************/

// Basic website info.
define("WEB_NAME", "__ENTER__NAME__HERE__"); // The name of the website.
define("TIMEZONE", "Europe/London"); // The timezone to use.  See http://php.net/manual/en/timezones.php

// Set the database information.
define("SQL_HOST", "localhost"); // The IP or location of the database server (include port if needed).
define("SQL_USER", ""); // The username for the database server. 
define("SQL_PASS", ""); // The password for the database server.
define("SQL_DB", ""); // The name of the database which we're using!
define("SQL_PREFIX", ""); // Enter a lowercase prefix if using a shared database for the tables.

// What authentication method are we using for this system?
define("AUTHENTICATION_FILE", "default.php"); // Just the filename from the auth folder.

// Teamspeak server settings.
define("TS_USER", "TS_BOT"); // The query user that this script will use.
define("TS_PASS", ""); // The password that the query user users.
define("TS_HOST", "teamspeak.my-server.co.uk");
define("TS_PORT", "9987"); // The SERVER port.
define("TS_QPORT", "10011"); // The QUERY port.

// Teamspeak account settings.
define("TS_MAX_ID", 5); // The maximum number of IDs a user can have at any one time.
define("TS_SA_GROUP", 6); // The group ID of server admins.
define("TS_ME_GROUP", 7); // The group ID of the normal members.

/************* DEFINE THE DIFFERENT RULES FOR THE TS BOT TO ENFORCE *************/
// Purge unused registrations and priviledge keys.
define("RULE_PURGE_KEYS", true); // If set to true, rule will be actioned.
define("RULE_PURGE_KEYS_DAYS", 5); // The number of days that one-time keys are valid for.

// Registration rule - kick any members that haven't registered their client ID.
define("RULE_REG", true);

// Idle rule - when the server is nearing capacity, users that are idle will be removed.
define("RULE_IDLE", true); // If set to true, will enforce the idle rule.
define("RULE_CAPACITY", 15); // The number of clients that must be connected, below the maximum number, before this rule applies.
define("RULE_IDLE_CHANNELS", serialize(array("Room 1", "Room 2"))); // The channels where the idle rule APPLIES. (Case sensitive).  Set to false for all channels.
define("RULE_IDLE_WARN", 20); // Total number of minutes someone should be idle for before the warnings start.
define("RULE_IDLE_KICK", 30); // Total number of minutes someone should be idle for before being kicked.

// Automatica Server Admin - When someone with the autoSA permission is detected, give them SA.
define("RULE_AUTO_SA", true); // Not yet valid.

// Check for updates
define("UPDATES", false); // CURRENTLY USELESS.

/******************************************************************************/
/*** END OF SETTINGS - ONLY MODIFY BELOW HERE IF YOU KNOW WHAT YOU'RE DOING ***/
/******************************************************************************/

?>
