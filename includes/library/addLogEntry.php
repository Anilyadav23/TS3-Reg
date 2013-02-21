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

function addLogEntry($memberID, $action, $info=array()){
    mysql_query("INSERT INTO `log`
                 (`memberID`, `action`, `details`, `timestamp`)
                 VALUES
                 ('".$memberID."', '".$action."', '".serialize($info)."', '".date("Y-m-d H:i:s")."')");
}

?>