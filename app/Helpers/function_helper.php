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

function getAvatar()
{
    // Path ของไฟล์ avatar
    $avatarPath = base_url("assets/images/users/");

    // ตรวจสอบว่าค่าที่ส่งเข้ามาว่างหรือไม่
    if (empty($inputAvatar)) {
        // Random ตัวเลขระหว่าง 1 ถึง 10
        $randomNumber = rand(1, 10);

        // สร้างชื่อไฟล์ของ avatar
        $randomAvatar = $avatarPath . "/avatar-" . $randomNumber . ".jpg";

        return $randomAvatar;
    } else {
        // ถ้าค่าที่ส่งเข้ามาไม่ว่าง ให้ return ค่านั้น
        return $inputAvatar;
    }
}

if (!function_exists('pr')) {

    function pr($data = [])
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
}

if (!function_exists('px')) {

    function px($data = [])
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        exit;
    }
}
