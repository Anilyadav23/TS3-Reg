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

// If an error is present, sort it.
$error = (isset($_GET["error"]) ? $_GET["error"] : "");
switch ($error) {
    case "login":
        $errorMsg = "The credentials provided are invalid.";
        break;

    case "netBanned":
        $errorMsg = "Your network/system account has been banned.  You cannot register for Teamspeak at this time.";
        break;

    case "banned":
        $errorMsg = "You have been blacklisted and are not permitted to access our server.";
        break;

    default:
        $errorMsg = "";
        break;
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

        <title>Virtual Controller Assistant - Administration</title>

        <link href="style/style.css" rel="stylesheet" type="text/css" />

        <link href="style/uni-form/uni-form.css" rel="stylesheet" type="text/css" />
        <link href="style/uni-form/default.uni-form.css" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
    </head>
    <body>
        <div class="content">
            <div id="header">[ <?= WEB_NAME ?> TEAMSPEAK REGISTRATION ]</div>
            <div id="main">
                <div class="center">
                    <noscript><p class="red">You currently have javascript disabled - javascript is used throughout this website and therefore it is crucial that you enable this before continuing.</p></noscript>
                    <div id='content'>
                        <div class='post'>
                            <div class='entry'>
                                <p>
                                    <?= WEB_NAME ?> uses TeamSpeak3 as a voice communication server for its members.   Before being able to access this server, you <strong>must</strong>
                                    register your client's ID with us to be granted access.
                                </p>
                                <p>
                                    <?= AUTHENTICATION_INFO ?>
                                </p>
                                <br />

                                <form method="post" action="index.php" class="uniForm">
                                    <fieldset class="inlineLabels">

                                        <?= displayErrors($errorMsg) ?>

                                        <div class="ctrlHolder">
                                            <label for="username">Username/ID:</label>
                                            <input name="username" id="memberid" size="35" type="text" class="textInput" />
                                        </div>

                                        <div class="ctrlHolder">
                                            <label for="password">Password:</label>
                                            <input name="password" id="password" size="35" type="password" class="textInput" />
                                        </div>
                                        <br />

                                        <div class="buttonHolder">
                                            <button type="submit" name="login" id="login" class="primaryAction">Login</button>
                                        </div>

                                    </fieldset>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </body>
</html>
