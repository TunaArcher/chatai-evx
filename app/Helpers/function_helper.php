<?php

function getPlatformIcon($platform)
{
    return match ($platform) {
        'Facebook' => 'ic-Facebook.png',
        'Line' => 'ic-Line.png',
        'WhatsApp' => 'ic-WhatsApp.png',
        'Instagram' => 'ic-Instagram.svg',
        'Tiktok' => 'ic-Tiktok.png',
        default => '',
    };
}
