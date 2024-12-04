<?php
function sendMessageToWebSocket(array $data)
{
    $url = getenv('WS_URL'); // URL ของ WebSocket Server
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // แปลงข้อมูลเป็น JSON
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // ไม่ต้องการรับ Response กลับ
    curl_exec($ch);
    curl_close($ch);
}
