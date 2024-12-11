<?php

function getPlatformIcon($platform)
{
    return match ($platform) {
        'Facebook' => 'ic-Facebook.png',
        'Line' => 'ic-Line.png',
        'WhatsApp' => 'ic-WhatsApp.png',
        default => '',
    };
}
