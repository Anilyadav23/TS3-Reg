<?php

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

?>