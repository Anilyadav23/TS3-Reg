<?php

die('This bot is no longer active');

require_once "/var/www/vatsim-uk.co.uk/teamspeak/head.php";

// Modify the include path so that we can use the TS3 scripts.
ini_set("include_path", "/var/www/sharedResources/:/var/www/sharedResources/TeamSpeak3/");

function getSubChannels($channel){
    // Default the channel array
    $channels = array();

    // Get the sub channels for the channel.
    $subChans = $channel->subChannelList();

    // Loop through and add to the array
    foreach($subChans as $sc){
        // Add this channel to the array.
        $channels[] = $sc;

        // Get the sub channels for this channel and merge with this array.
        $channels = array_merge($channels, getSubChannels($sc));
    }

    // Return all the channels
    return $channels;
}

// The array of channels that the idle time applies to.
$idleChannels = array("Arrivals Lounge", "Departure Lounge");

// Array of client database ids to skip when cycling through the clients.
$skipClients = array(
	"2", //Kai
	"1421", //Harry
	"1042", //Kieran
	"2052", //Kieran
	"2804" //Kieran - (greedy)
);

// Print title, date and time.
print "TS Bot Cronjob (".gmdate("Y-m-d H:i:s").")\n\n";

// Update the names that need updating.
print "Updating client data using CERT...\n";
$reg_q = mysql_query("SELECT *
                      FROM `".SQL_PREFIX."registrations`
                      GROUP BY `registrations`.`memberID`
                      ORDER BY `registrations`.`memberID` ASC");

     /*
      *  If the MySQL is down? Kill the script. 
      */
      if(!$reg_q){
          
            die(mysql_error());
          
      }

while($reg = mysql_fetch_object($reg_q)){
    // If the last cert check was more than 48 hours ago, get the data again.
    if($reg->lastCertCheck <= gmdate("Y-m-d H:i:s", strtotime("-48 hours")) && $reg->memberID != '707070'){
        // Get the XML feed.
        $xmlFeed = simplexml_load_file("https://cert.vatsim.net/vatsimnet/idstatusint.php?cid=".$reg->memberID);

        // Update the data.
        $name = $xmlFeed->user->name_first;
        $name.= " ".$xmlFeed->user->name_last;
        mysql_query("UPDATE `registrations`
                     SET `certRating` = '".$xmlFeed->user->rating."',
                         `lastCertCheck` = '".gmdate("Y-m-d H:i:s")."'
                     WHERE `memberID` = '".$reg->memberID."'");

        print "\tClient ".$reg->memberID." updated (".mysql_affected_rows()." time(s)): ".$name."\n";
    }
}
print "END***\n\n";

// Connect to the TS server.
$virt = TeamSpeak3::factory($teamspeak_serverQuery);

// Get server info.
$serverInfo = $virt->getInfo();

// Echo out a ban list.
$getBans = $virt->banList();

foreach($getBans as $key => $value){
	//print_r($value);
}
//print_r($getBans);


// Get the maximum number of clients and number of clients online.
$maxClients = $serverInfo["virtualserver_maxclients"]-$serverInfo["virtualserver_reserved_slots"];
$clientsOnline = $serverInfo["virtualserver_clientsonline"];

// Set the Bot's nickname.
$virt->whoamiSet("client_nickname", WEB_NAME." TS BOT");

// Connect to the TS update server.
$updateServer = TeamSpeak3::factory("update");

//print_r($virt->clientList());
//client_unique_identifier
//2
//$virt->serverGroupClientAdd(6, 22);

// ONLY IF THE SERVER IS NEARING CAPACITY (i.e, it's limit-reserved-15).
print "IS server nearing capacity? Clients online (".$clientsOnline.") >= (Maximum clients (".$maxClients.") minus 15 = ".($maxClients-15)."): ";
// First we want to do an idle check (if they're idle, we don't really care about anything else).
// So, loop through the idle channels and get everyone in them!
if($clientsOnline >= $maxClients-15){
    print "YES!\n";
    foreach($idleChannels as $cName){
        // Get the channel and add it to the array.
        print "Getting ".$cName." ID: ";
        $channel = $virt->channelGetByName($cName);
        print $channel.".\n";

        // Get all the sub channels
        print "Getting sub-channels for ".$cName.": \n\t";
        $channels = array_merge(array($channel), getSubChannels($channel));

        // Go through all channels and kick idle people!
        foreach($channels as $chan){
            // Get the client list
            $clients = $chan->clientList();

            // Go through all clients to see if they're idle.
            foreach($clients as $client){
                // Get idle time
                $idleTime = $client["client_idle_time"]/1000;

                // If it's greater than 30 mins, kick
                if($idleTime >= (30*60)){
                    print "Client ".$client["client_nickname"]." (".$client["client_unique_identifier"].") was idle - kicked.";
                    
                    // Perform kick
                    $client->kick(TeamSpeak3::KICK_SERVER, "Idle in excess of 30 minutes!");

                    // Log the kick
                    mysql_query("INSERT INTO `kicks`
                                 (`uniqueID`, `memberID`,
                                  `reason`, `timestamp`)
                                 VALUES
                                 ('".$client["client_unique_identifier"]."', '0'
                                  'idle_30_mins', '".gmdate("Y-m-d G:i:s")."')");
                }
            }
        }
    }
} else {
    print "NO\n";
}
print "END***\n\n\n";

// Default the "online client" list.
// This contains an array of information for those folk that're currently connected.
$onlineClients = array();

// Loop through the list of clients on the server.
print "Looping through clients:";
foreach($virt->clientList() as $client)
{
    // skip query clients
    if($client["client_type"]) continue;
    print "\n\t".str_pad($client, 20, " ", STR_PAD_RIGHT)." -> ";

    // Get extended information
    $clientInfo = $client->getInfo();
	
	// Is the db id in the skip array.
	if(in_array($clientInfo['client_database_id'], $skipClients)) continue;

    // Get the unique ID of this client.
    $uniqueID = $client["client_unique_identifier"];
    print "UniqueID=".$uniqueID."\t\t";

    // Get the details for this ID in the database.
    $member_q = mysql_query("SELECT *
                             FROM `".SQL_PREFIX."registrations`
                             WHERE `uniqueID` = '".$uniqueID."'
                               AND `deleted` = '0'");
    $member = mysql_fetch_object($member_q);
	
	//if($member->memberID == 1057100) continue;

    // If they don't exist, remove them.
    if(mysql_num_rows($member_q) < 1){
        print "reg not found - ";
        // Check we haven't advised them recently (i.e there's not one still active).  If not, do so.
        $adviseCheck_q = mysql_query("SELECT *
                                      FROM `".SQL_PREFIX."advisories`
                                      WHERE `uniqueID` = '".$uniqueID."'
                                        AND `type` = 'no_reg'
                                        AND `timestamp` LIKE '".gmdate("Y-m-d")."%'");
        if(mysql_num_rows($adviseCheck_q) < 1){
            // Advise them.
            mysql_query("INSERT INTO `advisories`
                         (`uniqueID`, `type`,
                          `expire`, `timestamp`)
                         VALUES
                         ('".$uniqueID."', 'no_reg',
                          '".gmdate("Y-m-d G:i:s", strtotime("+30 minutes"))."', '".gmdate("Y-m-d G:i:s")."')");
            
            print "advising.";
            
            $client->message("[COLOR=red]Your Teamspeak ID isn't registered on our server.[/COLOR]");
            $client->message("[COLOR=red]If you do not register your Teamspeak ID within 30 minutes[/COLOR]");
            $client->message("[COLOR=red]you will be removed from the server.[/COLOR]");
            $client->message("[COLOR=red]To register, go to http://www.vatsim-uk.co.uk/teamspeak/[/COLOR]");
            $client->message("[COLOR=red]and follow the instructions.[/COLOR]");
            $client->message("[COLOR=red]Any issues should be sent to web-services@vatsim-uk.co.uk[/COLOR]");
            
            print " Done.";
            
            continue;
        }
        
        // Do they need kicking yet?
        $adviseCheck_q = mysql_query("SELECT *
                                      FROM `".SQL_PREFIX."advisories`
                                      WHERE `uniqueID` = '".$uniqueID."'
                                        AND `type` = 'no_reg'
                                        AND `expire` <= '".gmdate("Y-m-d G:i:s")."'
                                      ORDER BY `timestamp` ASC
                                      LIMIT 1");
        if(mysql_num_rows($adviseCheck_q)){
            print "kicking.";
            
            $client->message("[COLOR=red]Your Teamspeak ID isn't registered on our server.[/COLOR]");
            $client->message("[COLOR=red]You were given 30 minutes to register your client and failed to do so.[/COLOR]");
            $client->message("[COLOR=red]You're now being removed from the server.[/COLOR]");
            $client->message("[COLOR=red]Register at http://www.vatsim-uk.co.uk/teamspeak/ before attempting to reconnect.[/COLOR]");
            $client->message("[COLOR=red]Any issues should be sent to web-services@vatsim-uk.co.uk[/COLOR]");
            
            $client->kick(TeamSpeak3::KICK_SERVER, "No registration - register at http://www.vatsim-uk.co.uk/teamspeak/");
            $client->deleteDb();
            
            mysql_query("INSERT INTO `kicks`
                         (`uniqueID`, `reason`, `timestamp`)
                         VALUES
                         ('".$uniqueID."', 'no_reg', '".gmdate("Y-m-d G:i:s")."')");

            print " Done.";
            
            continue;
        }
        
        // Still in amnesty...
        print "amnesty is still valid.";
        
        // Continue to next client
        continue;
    }

    print "CID=".$member->memberID."\t";

    // Log the IP being used for this client.
    if($clientInfo["connection_client_ip"]){
        // We don't need to log it again if we already have this IP.
        // Instead, we can increment the times it's been used by this person?
        $check_q = mysql_query("SELECT * FROM `".SQL_PREFIX."log_ips`
                                WHERE `memberID` = '".$member->memberID."'
                                  AND `ip` = '".$clientInfo["connection_client_ip"]."'");

        // If we have one, increment the count.
        if(mysql_num_rows($check_q) > 0){
            // Get the row.
            $logIP = mysql_fetch_object($check_q);

            // Increment.
            mysql_query("UPDATE `log_ips`
                         SET `timesUsed` = `timesUsed`+1,
                             `lastUsed` = '".gmdate("Y-m-d G:i:s")."'
                         WHERE `logIPID` = '".$logIP->logIPID."'");
        } else {
            // Add it.
            mysql_query("INSERT INTO `log_ips`
                         (`uniqueID`, `memberID`,
                          `ip`, `timesUsed`,
                          `lastUsed`, `firstUsed`)
                         VALUES
                         ('".$uniqueID."', '".$member->memberID."',
                          '".$clientInfo["connection_client_ip"]."', '1',
                          '".gmdate("Y-m-d G:i:s")."', '".gmdate("Y-m-d G:i:s")."')");
        }
        print "IP=".$clientInfo["connection_client_ip"]."\t";
    } else {
        print "IP=Unknown\t";
    }

    // Has this member also used a different ID in the last minute or two?
    // If so, it's highly likely that it's not them, so kick them!
    $dupe_q = mysql_query("SELECT * FROM `".SQL_PREFIX."log_ips`
                           WHERE `memberID` = '".$member->memberID."'
                             AND `ip` != '".$clientInfo["connection_client_ip"]."'
                             AND `lastUsed` >= '".gmdate("Y-m-d G:i:s", strtotime("-2 minutes"))."'");

    // Dupes?
    if(mysql_num_rows($dupe_q) > 0){
        // Get the info.
        $dupes = array();
        while($dupe = mysql_fetch_object($dupe_q)){
            $dupes[] = $dupe;
        }

        // Output the duplicate connections.
        print "Dupe connections from: ";
        $d = "";
        foreach($dupes as $dupe){
            $d = $dupe->ip.", ";
        }
        $d = rtrim($d, ", ");
        print $d.".";

        // Ban reason
        $reason = "Flouting of the rules: Multiple connections to the server from different IPs (within a 2 minute period) registered to the same address.";

        // Let's ban all the IPs and unique IDs associated with this "incident",
        // Then let's kick them all.
        /*foreach($dupes as $dupe){
            // Ban the IP
            mysql_query("INSERT INTO `banned`
                         (`banCriteria`, `banBy`, `banReason`,
                          `banExpires`, `timestamp`)
                         VALUES
                         ('".$dupe->ip."', 'VATUK Teamspeak Bot', '".$reason."',
                          '".gmdate("Y-m-d G:i:s", strtotime("+24 hours"))."', '".gmdate("Y-m-d G:i:s")."')");

            // Ban the unique ID.
            mysql_query("INSERT INTO `banned`
                         (`banCriteria`, `banBy`, `banReason`,
                          `banExpires`, `timestamp`)
                         VALUES
                         ('".$dupe->uniqueID."', 'VATUK Teamspeak Bot', '".$reason."',
                          '".gmdate("Y-m-d G:i:s", strtotime("+24 hours"))."', '".gmdate("Y-m-d G:i:s")."')");
        }*/

        // Add the ban to the database for the member: We're banning their CID, IP and Unique ID.
        // CID
        mysql_query("INSERT INTO `banned`
                     (`banCriteria`, `banBy`, `banReason`,
                      `banExpires`, `timestamp`)
                     VALUES
                     ('".$member->memberID."', 'VATUK Teamspeak Bot', '".$reason."',
                      '".gmdate("Y-m-d G:i:s", strtotime("+24 hours"))."', '".gmdate("Y-m-d G:i:s")."')");
        // IP.
        mysql_query("INSERT INTO `banned`
                     (`banCriteria`, `banBy`, `banReason`,
                      `banExpires`, `timestamp`)
                     VALUES
                     ('".$clientInfo["connection_client_ip"]."', 'VATUK Teamspeak Bot', '".$reason."',
                      '".gmdate("Y-m-d G:i:s", strtotime("+24 hours"))."', '".gmdate("Y-m-d G:i:s")."')");
        // Unique ID.
        mysql_query("INSERT INTO `banned`
                     (`banCriteria`, `banBy`, `banReason`,
                      `banExpires`, `timestamp`)
                     VALUES
                     ('".$uniqueID."', 'VATUK Teamspeak Bot', '".$reason."',
                      '".gmdate("Y-m-d G:i:s", strtotime("+24 hours"))."', '".gmdate("Y-m-d G:i:s")."')");

        // Now delete all registrations.
        mysql_query("UPDATE `registrations`
                     SET `deleted` = '1',
                         `deletedReason` = 'duplicate_connections',
                         `deletedIP` = '127.0.0.1',
                         `deletedTimestamp` = '".gmdate("Y-m-d H:i:s")."'
                     WHERE `memberID` = '".$member->memberID."'
                       AND `deleted` = '0'");

        // Inform the member they're being booted and banned for 24 hours.
        $client->message("[COLOR=red]It appears that you are currently connected from more than one location.[/COLOR]");
        $client->message("[COLOR=red]At present, you currently have ".(count($dupes)+1)." IPs active on our Teamspeak Server[/COLOR]");
        $client->message("[COLOR=red]registered to this account.[/COLOR]");
        $client->message("[COLOR=red]As such, all of your registrations have now been terminated[/COLOR]");
        $client->message("[COLOR=red]and your VATSIM CID has been banned from our server for a period of 24 hours.[/COLOR]");
        $client->message("[COLOR=red]You may reconnect after ".gmdate("d/m/Y G:i:s")."[/COLOR]");
        $client->message("[COLOR=red]Continued flouting of the rules will result in your access being curtailed on a more permenant basis.[/COLOR]");
        $client->message("[COLOR=red]Any issues should be sent to web-services@vatsim-uk.co.uk[/COLOR]");

        // Kick them.
        $client->kick(TeamSpeak3::KICK_SERVER, "Duplicate connections.");
        $client->deleteDb();

        // Log the kick.
        mysql_query("INSERT INTO `kicks`
                     (`uniqueID`, `memberID`,
                      `reason`, `timestamp`)
                     VALUES
                     ('".$uniqueID."', '".$member->memberID."'
                      'duplicate_connections', '".gmdate("Y-m-d G:i:s")."')");
    }

    // Let's just double check that they're not banned in our local database.
    // If they are, we'll remove them.
    $ban_q = mysql_query("SELECT * FROM `".SQL_PREFIX."banned`
                          WHERE (`banCriteria` = '".$member->memberID."'
                                 OR `banCriteria` = '".$uniqueID."')
                            AND `banExpires` <= '".gmdate("Y-m-d G:i:s")."'");

    // Are they banned?
    if(mysql_num_rows($ban_q) > 0){
        print " Member ".$member->memberID." is locally banned.  Kicking and deleting reg(s).";

        // Get info
        $ban = mysql_fetch_object($ban_q);

        // Now inform them - if it's permenant, the message is different.
        $client->message("[COLOR=red]You are currently banned from the VATSIM-UK Teamspeak Server.[/COLOR]");
        $client->message("[COLOR=red]This ban has been enforced by ".$ban->bannedBy."[/COLOR]");
        if($ban->banExpires == "0000-00-00 00:00:00"){
            $client->message("[COLOR=red]and is permenant.[/COLOR]");
        } else {
            $client->message("[COLOR=red]between ".$ban->timestamp." and ".$ban->banExpires.".[/COLOR]");
        }
        $client->message("[COLOR=red]The reason for this ban is as follows:[/COLOR]");
        $client->message("[COLOR=red]".$ban->banReason."[/COLOR]");
        $client->message("[COLOR=red]You are not permitted to use the VATSIM-UK Teamspeak Server in this time.[/COLOR]");
        $client->message("[COLOR=red]Do not try to re-connect.[/COLOR]");
        $client->message("[COLOR=red]Any issues should be sent to web-services@vatsim-uk.co.uk[/COLOR]");

        // Now remove them.
        $client->kick(TeamSpeak3::KICK_SERVER, "Banned from Teamspeak.");
        $client->deleteDb();

        // Log kick
        mysql_query("INSERT INTO `kicks`
                     (`uniqueID`, `memberID`,
                      `reason`, `timestamp`)
                     VALUES
                     ('".$uniqueID."', '".$member->memberID."'
                      'local_ban', '".gmdate("Y-m-d G:i:s")."')");
    }
    
    // Since they do exist, we'll double check they're not network banned.
    if($member->certRating == 0 && $member->lastCertCheck != "0000-00-00 00:00:00"){
        print " Member ".$member->memberID." is network banned.  Kicking and deleting reg(s).";

        // Inform them.
        $client->message("[COLOR=red]Your VATSIM account is currently suspended.[/COLOR]");
        $client->message("[COLOR=red]We don't allow suspended members to access our services[/COLOR]");
        $client->message("[COLOR=red]and as such you are being removed from the server.[/COLOR]");
        $client->message("[COLOR=red]Your registration has now been deleted and you are not[/COLOR]");
        $client->message("[COLOR=red]able to register a new client ID until your suspension has been lifted.[/COLOR]");
        $client->message("[COLOR=red]Any issues should be sent to web-services@vatsim-uk.co.uk[/COLOR]");

        // Kick them.
        $client->kick(TeamSpeak3::KICK_SERVER, "Banned from VATSIM network.");
        $client->deleteDb();

        // Log the kick.
        mysql_query("INSERT INTO `kicks`
                     (`uniqueID`, `memberID`,
                      `reason`, `timestamp`)
                     VALUES
                     ('".$uniqueID."', '".$member->memberID."'
                      'network_banned', '".gmdate("Y-m-d G:i:s")."')");
        
        //mysql_query("DELETE FROM `".SQL_PREFIX."registrations`
        //             WHERE `memberID` = '".$member->memberID."'");
        mysql_query("UPDATE `registrations`
                     SET `deleted` = '1',
                         `deletedReason` = 'network_ban',
                         `deletedIP` = '127.0.0.1',
                         `deletedTimestamp` = '".gmdate("Y-m-d H:i:s")."'
                     WHERE `memberID` = '".$member->memberID."'
                       AND `deleted` = '0'");
        
        print " Done.";
        
        continue;
    }
	
	// Calcualte idle time in minutes.
	$clientIdleTime = ((($client['client_idle_time']/1000)/60));
	$clientIdleTime = intval($clientIdleTime);
	
	// Switch up the idle time.
	if($clientIdleTime >= 60 && $clientIdleTime <= 75){
		$client->clientPoke($client['clid'], "You've been idle for around an hour, are you still alive?");
	} elseif($clientIdleTime > 80 && $clientIdleTime <= 95){
		$client->clientPoke($client['clid'], "Yo, wake up an un-idle yourself!");
	} elseif($clientIdleTime > 115 && $clientIdleTime <= 119){
		$client->clientPoke($client['clid'], "Your gonna be kicked for being idle soon, wake up lazy!");
	}
	
	if("client_description" != $member->certName." (".$member->memberID.")" && $client['client_database_id'] != 1085){
		$properties = array("client_description" => $member->certName." (".$member->memberID.")");
		$virt->clientModifyDb($client['client_database_id'], $properties);
	}
	
    // Does their nickname match their vastsim registered one?
    if((strcasecmp(trim($client["client_nickname"]), trim($member->certName)) != 0)){
		$client->message("[COLOR=red]Your nickname does not match your VATSIM registered name.[/COLOR]");
		$client->message("[COLOR=red]If you repeatedly use an incorrect nickname, your account will be banned.[/COLOR]");

		// Client being kicked output
        print "\n\tNickname doesn't match VATSIM name. Kicking client: ".$client["client_nickname"];
		
        // Log the kick
        mysql_query("INSERT INTO `kicks`
                     (`uniqueID`, `memberID`,
                      `reason`, `timestamp`)
                     VALUES
                     ('".$client["client_unique_identifier"]."', '".$member->memberID."',
                      'nickname_does_not_match_cert', '".gmdate("Y-m-d G:i:s")."')");
        
        // Perform kick
        $client->kick(TeamSpeak3::KICK_SERVER, "Nickname is incorrect.");
		
		continue;
        
    }

}
print "\nEND***";
print "\n\n";

// Loop through a list of priv keys on the server.
try {
    foreach($virt->privilegeKeyList() as $privKey){
        // Get the token
        $token = $privKey["token"];

        // Query the database for the token.
        $token_q = mysql_query("SELECT *
                                FROM `".SQL_PREFIX."registrations`
                                WHERE `token` = '".mysql_real_escape_string($token)."'
                                  AND `deleted` = '0'");

        // If it doesn't exist or it's expired, delete it
        if(mysql_num_rows($token_q) < 1){
            // Delete the token from TS.
            $virt->privilegeKeyDelete($token);
            print "Token ".$token." doesn't exist in our database - deleted.\n";

            // Continue to the next one
            continue;
        }

        // Get the token
        $tokenRow = mysql_fetch_assoc($token_q);

        // If it's expired, delete it.
        if($tokenRow["timestamp"] <= gmdate("Y-m-d H:i:s", strtotime("-10 days"))){
            // Delete the token from TS.
            $virt->privilegeKeyDelete($token);

            // Delete from the database
            mysql_query("UPDATE `registrations`
                         SET `deleted` = '1',
                             `deletedReason` = 'reg_expired',
                             `deletedIP` = '127.0.0.1',
                             `deletedTimestamp` = '".gmdate("Y-m-d H:i:s")."'
                         WHERE `registrationID` = '".$tokenRow->registrationID."'");

            print "Token ".$token." has expired - deleted.\n";

            // Continue to the next one
            continue;
        }
    }
} catch(Exception $e){
    print "NO tokens to remove.\n";
}

?>