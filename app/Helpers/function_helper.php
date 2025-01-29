<?php

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

function getPlatformIcon($platform)
{
    return match (strtolower($platform)) {
        'facebook' => 'ic-Facebook.png',
        'line' => 'ic-Line.png',
        'whatsapp' => 'ic-WhatsApp.png',
        'instagram' => 'ic-Instagram.svg',
        'tiktok' => 'ic-Tiktok.png',
        default => 'ic-default.png',
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

// อัปโหลดไฟล์ไปยัง DigitalOcean Spaces
function uploadToSpaces($fileContent, $fileName)
{
    $s3 = new S3Client([
        'version' => 'latest',
        'region' => getenv('REGION'), // S3-compatible, region ไม่สำคัญเพราะเราใช้ endpoint เอง
        'endpoint' => getenv('ENDPOINT'),
        'use_path_style_endpoint' => false,
        'credentials' => [
            'key' => getenv('KEY'),
            'secret' =>  getenv('SECRET_KEY')
        ],
        'suppress_php_deprecation_warning' => true, // ปิดข้อความเตือน
    ]);

    try {

        $result = $s3->putObject([
            'Bucket' => getenv('S3_BUCKET'),
            'Key' => 'uploads/img_autoconx/' . $fileName,
            'Body' => $fileContent,
            'ACL' => 'public-read' // ตั้งค่าให้ไฟล์เป็น public
        ]);

        return $result['ObjectURL']; // คืน URL ของไฟล์ที่อัปโหลด
    } catch (AwsException $e) {
        throw new Exception("Failed to upload to Spaces: " . $e->getMessage());
    }
}

function fetchFileFromWebhook($url, $headers = [])
{
    try {
        // ใช้ cURL เพื่อดึงข้อมูลไฟล์จาก URL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // ถ้ามี Header (เช่น LINE ต้องใช้ Authorization)
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $fileContent = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // ตรวจสอบว่า HTTP Status เป็น 200 หรือไม่
        if ($httpCode === 200) {
            return $fileContent;
        } else {
            throw new Exception("Failed to fetch file. HTTP Status: {$httpCode}");
        }
    } catch (Exception $e) {
        throw new Exception("Error fetching file: " . $e->getMessage());
    }
}