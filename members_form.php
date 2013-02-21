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

// Get the registered clients for them.
$query = mysql_query("SELECT * FROM `".SQL_PREFIX."registrations`
                      WHERE `memberID` = '" . mysql_real_escape_string($memberID) . "'
                        AND `deleted` = '0'
                      ORDER BY `registeredTimestamp` DESC");
$registrations = array();
while ($registration = mysql_fetch_object($query)) {
    $registrations[] = $registration;
}

// If an error is present, sort it.
$error = (isset($_GET["error"]) ? $_GET["error"] : $error);
switch ($error) {
    case "invalid_client":
        $errorMsg = "You specified an invalid client ID for removal.";
        break;

    case "del_flood":
        $errorMsg = "The TeamSpeak3 server is currently receiving a lot of requests and therefore could not delete your registration at this time.  Please try again in a few moments.";
        break;
    
    case "max_reg":
        $errorMsg = "You have reached the maximum number of registrations you are allowed.  Please delete a registration before attempting to create a new one.";
        break;

    case "already_reg":
        $errorMsg = "This unique ID has already been attached to an account.  Unique ID's by their very nature, are unique to an individual user.";
        break;

    case "banned":
        $errorMsg = "This unique ID has been banned from the TeamSpeak3 server.";
        break;

    default:
        $errorMsg = "";
        break;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- DEVELOPED BY ANTHONY LAWRENCE <freelancer][at][anthonylawrence.me.uk>. -->
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

        <title><?=WEB_NAME?> TEAMSPEAK REGISTRATION</title>

        <link href="style/style.css" rel="stylesheet" type="text/css" />

        <link href="style/uni-form/uni-form.css" rel="stylesheet" type="text/css" />
        <link href="style/uni-form/default.uni-form.css" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
    </head>

    <body>
        <div class="content">
            <div id="header">[ <?=WEB_NAME?> TEAMSPEAK REGISTRATION ]</div>
            <div id="main">
                <div class="center">
                    <noscript><p class="red">You currently have javascript disabled - javascript is used throughout this website and therefore it is crucial that you enable this before continuing.</p></noscript>
                    <div id='content'>
                        <div class='post'>
                            <div class='entry'>
                                <h2>Current Registration(s)</h2>
                                <p>
                                    Below are a list of current registrations for the <?=WEB_NAME?> Teamspeak Server.
                                    You currently have a total of <strong><?=count($registrations)?></strong> of a possible
                                    <strong><?=TS_MAX_ID?></strong> registrations.  You therefore have <strong><?=(TS_MAX_ID - count($registrations))?></strong> registrations
                                    remaining.
                                </p>
                                <p>
                                    <strong>Please Note:</strong> Removing a client ID from this list will also delete the associated one-time key
                                    (if it hasn't been used) and remove the client from the TeamSpeak3 membership database. You will no longer be
                                    able to connect using this ID and if you are currently connected, you <strong>will be disconnected</strong>.
                                </p>
                                <form method="get" action="#" class="uniForm">
                                    <fieldset class="inlineLabels">

                                    <?= displayErrors($errorMsg) ?>

                                    <?php if (isset($_GET['delSuccess'])): ?>
                                        <div id='okMsg'><p>The client ID was removed successfully!</p></div>
                                    <?php endif; ?>

                                    <?php if (isset($_GET['newSuccess'])): ?>
                                        <div id='okMsg'>
                                            <p>
                                                You have successfully registered your unique ID!<br />
                                                Your one-time key is:
                                                <?=  urldecode($_GET["newSuccess"])?>
                                            </p>
                                        </div>
                                    <?php endif; ?>

                                        <table style="border-style: solid; border-color: #000000;" width="100%">
                                            <tr>
                                                <th align="center">Client ID</th>
                                                <th align="center">One-Time Key</th>
                                                <th align="center">Registration Date</th>
                                                <th align="center">Remove</th>
                                            </tr>
                                            <?php if (count($registrations) > 0): ?>
                                                <?php foreach ($registrations as $registration): ?>
                                                    <tr>
                                                        <td align="center"><?= $registration->uniqueID ?></td>
                                                        <td align="center"><?= $registration->token ?></td>
                                                        <td align="center"><?= $registration->registeredTimestamp ?></td>
                                                        <td align="center"><a href="members_deleteKey.php?registrationDelete=<?= urlencode($registration->registrationID) ?>">Remove</a></td>
                                                    </tr>
                                                    <tr>
                                                        <td align="center" colspan="4">
                                                            <strong>One-Time Key:</strong>
                                                            <?=$registration->token?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                    <tr>
                                                        <td colspan="4" align="center">You have no clients registered.</td>
                                                    </tr>
                                            <?php endif; ?>
                                        </table>
                                    </fieldset>
                                </form>
                                <br />
                                
                                <h2>New Registration</h2>
                                <p>
                                    If you wish to add a new registration to the database, enter your client's UNIQUE ID below.
                                </p>
                                <p>
                                    To locate your Unique ID:
                                    <ol>
                                        <li>Start TeamSpeak3.</li>
                                        <li>Click on "settings".</li>
                                        <li>Click on "identities".</li>
                                        <li>Select "default" and copy the unique ID that is displayed.</li>
                                    </ol>
                                </p>
                                <form method="post" action="members_newKey.php" class="uniForm">
                                    <fieldset class="inlineLabels">

                                        <div class="ctrlHolder">
                                            <label for="uniqueID">Unique ID:</label>
                                            <input name="uniqueID" id="uniqueID" size="35" type="text" class="textInput" />
                                        </div>
                                        <br />

                                        <div class="buttonHolder">
                                            <button type="submit" name="register" id="register" class="primaryAction">Register</button>
                                        </div>
                                    </fieldset>
                                </form>
                                <br />
                                
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </body>
</html>