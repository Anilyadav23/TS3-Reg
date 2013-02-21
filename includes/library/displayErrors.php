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

function displayErrors($errorMessage){
    if($errorMessage == ""){
        return false;
    }

    // Start creating the output
    $output = '';
    $output.= "<div id='errorMsg'>";

    // Output the error
    $output.= "<big style='margin-bottom: 8px;'><strong>An error occured!</strong></big>";
    $output.= "<p>".$errorMessage."</p>";

    $output.= "</div>";

    // Return the output
    return $output;
}


?>