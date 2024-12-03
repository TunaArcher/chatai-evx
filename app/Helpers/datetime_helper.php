<?php

function timeElapsed($datetime)
{
    // กำหนดเขตเวลา
    date_default_timezone_set('Asia/Bangkok');

    // แปลงวันที่ที่ให้มาเป็น timestamp
    $givenTime = strtotime($datetime);
    $currentTime = time();

    // คำนวณเวลาที่ผ่านไป
    $timeDifference = $currentTime - $givenTime;

    // แปลงค่าที่ผ่านเป็นหน่วย
    $minutes = floor($timeDifference / 60);
    $hours = floor($timeDifference / 3600);
    $days = floor($timeDifference / 86400);
    $months = floor($timeDifference / 2592000); // 30 วันโดยประมาณ

    // เลือกแสดงผลในรูปแบบข้อความ
    if ($minutes < 60) {
        return "$minutes นาทีที่แล้ว";
    } elseif ($hours < 24) {
        return "$hours ชั่วโมงที่แล้ว";
    } elseif ($days < 30) {
        return "$days วันที่แล้ว";
    } else {
        return "$months เดือนที่แล้ว";
    }
}