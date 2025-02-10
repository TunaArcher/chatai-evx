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
function _uploadToSpaces($fileContent, $fileName)
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
            'Key' => 'uploads/img/' . $fileName,
            'Body' => $fileContent,
            'ACL' => 'public-read' // ตั้งค่าให้ไฟล์เป็น public
        ]);

        return $result['ObjectURL']; // คืน URL ของไฟล์ที่อัปโหลด
    } catch (AwsException $e) {
        throw new Exception("Failed to upload to Spaces: " . $e->getMessage());
    }
}

// อัปโหลดไฟล์ไปยัง DigitalOcean Spaces และแยกโฟลเดอร์ตามประเภทไฟล์และแพลตฟอร์ม
function uploadToSpaces($fileContent, $fileName, $fileType, $platform)
{
    $platform = strtolower($platform);

    $s3 = new S3Client([
        'version' => 'latest',
        'region' => getenv('REGION'), // S3-compatible, region ไม่สำคัญเพราะเราใช้ endpoint เอง
        'endpoint' => getenv('ENDPOINT'),
        'use_path_style_endpoint' => false,
        'credentials' => [
            'key' => getenv('KEY'),
            'secret' => getenv('SECRET_KEY')
        ],
        'suppress_php_deprecation_warning' => true, // ปิดข้อความเตือน
    ]);

    // ตรวจสอบและกำหนดโฟลเดอร์ตามประเภทไฟล์
    switch ($fileType) {
        case 'image':
            $folder = "uploads/img/{$platform}/";
            break;
        case 'audio':
            $folder = "uploads/audio/{$platform}/";
            break;
        default:
            $folder = "uploads/others/"; // เผื่อไว้สำหรับประเภทอื่น ๆ ในอนาคต
    }

    try {
        $result = $s3->putObject([
            'Bucket' => getenv('S3_BUCKET'),
            'Key' => $folder . $fileName, // อัปโหลดไปยังโฟลเดอร์ที่เหมาะสม
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

function speechToText($filePath)
{
    $apiKey = getenv('GOOGLE_CLOUD_API_KEY');
    $audioFile = base64_encode(file_get_contents($filePath));

    $data = [
        "config" => [
            "encoding" => "LINEAR16",
            "sampleRateHertz" => 16000,
            "languageCode" => "th-TH",
            "alternativeLanguageCodes" => ["en-US"]
        ],
        "audio" => [
            "content" => $audioFile
        ]
    ];

    $ch = curl_init("https://speech.googleapis.com/v1/speech:recognize?key=$apiKey");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    log_message('info', "Debug ข้อความที่ได้หลัง S2T: " . json_encode($result, JSON_PRETTY_PRINT));

    return $result["results"][0]["alternatives"][0]["transcript"] ?? "";
}

function _convertAudioToText($audioUrl)
{
    // สร้างโฟลเดอร์ audio ถ้ายังไม่มี
    if (!file_exists('audio')) {
        mkdir('audio', 0777, true);
    }

    // ตั้งชื่อไฟล์แบบสุ่ม
    $uniqueId = uniqid('audio_');
    $m4aFile = "audio/{$uniqueId}.m4a";
    $wavFile = "audio/{$uniqueId}.wav";

    // ✅ ดาวน์โหลดไฟล์เสียงจาก DigitalOcean Spaces
    file_put_contents($m4aFile, file_get_contents($audioUrl));

    // ✅ ตรวจสอบว่าอยู่ใน Development Environment (Windows)
    $command = "ffmpeg -i $m4aFile -ar 16000 -ac 1 -c:a pcm_s16le $wavFile";
    if (getenv('CI_ENVIRONMENT') === 'development') {
        $ffmpegPath = "C:\\ffmpeg\\bin\\ffmpeg.exe"; // Windows ใช้ full path
        $command = "\"$ffmpegPath\" -i $m4aFile -ar 16000 -ac 1 -c:a pcm_s16le $wavFile";
    }

    // ✅ ใช้ ffmpeg แปลงไฟล์เสียงเป็น WAV
    exec($command, $output, $returnCode);

    if ($returnCode !== 0) {
        unlink($m4aFile); // ลบไฟล์ที่ดาวน์โหลดมา
        return "เกิดข้อผิดพลาดในการแปลงไฟล์เสียง";
    }

    // ✅ แปลงเสียงเป็นข้อความโดยใช้ Google Speech-to-Text API
    $text = speechToText($wavFile);

    // ✅ ลบไฟล์เสียงหลังจากแปลงเสร็จ
    unlink($m4aFile);
    unlink($wavFile);

    return $text;
}

function convertAudioToText($audioUrl, $platform)
{
    // สร้างโฟลเดอร์ audio ถ้ายังไม่มี
    if (!file_exists('audio')) {
        mkdir('audio', 0777, true);
    }

    // ตั้งชื่อไฟล์แบบสุ่ม
    $uniqueId = uniqid('audio_');

    // ตรวจสอบประเภทไฟล์ตามแพลตฟอร์ม
    switch (strtolower($platform)) {
        case 'line':
            $audioExtension = 'm4a';
            break;
        case 'facebook':
            $audioExtension = 'mp4';
            break;
        case 'whatsapp':
        case 'instagram':
            $audioExtension = 'ogg'; // กำหนดค่าเริ่มต้นที่สามารถเปลี่ยนแปลงได้ในอนาคต
            break;
        default:
            return "ไม่รองรับแพลตฟอร์ม: {$platform}";
    }

    $sourceFile = "audio/{$uniqueId}.{$audioExtension}";
    $wavFile = "audio/{$uniqueId}.wav";

    // ✅ ดาวน์โหลดไฟล์เสียงจาก DigitalOcean Spaces
    file_put_contents($sourceFile, file_get_contents($audioUrl));

    // ✅ ตรวจสอบว่าอยู่ใน Development Environment (Windows)
    $command = "ffmpeg -i $sourceFile -ar 16000 -ac 1 -c:a pcm_s16le $wavFile";
    if (getenv('CI_ENVIRONMENT') === 'development') {
        $ffmpegPath = "C:\\ffmpeg\\bin\\ffmpeg.exe"; // Windows ใช้ full path
        $command = "\"$ffmpegPath\" -i $sourceFile -ar 16000 -ac 1 -c:a pcm_s16le $wavFile";
    }

    // ✅ ใช้ ffmpeg แปลงไฟล์เสียงเป็น WAV
    exec($command, $output, $returnCode);

    if ($returnCode !== 0) {
        unlink($sourceFile); // ลบไฟล์ที่ดาวน์โหลดมา
        return "เกิดข้อผิดพลาดในการแปลงไฟล์เสียงจาก {$platform}";
    }

    // ✅ แปลงเสียงเป็นข้อความโดยใช้ Google Speech-to-Text API
    $text = speechToText($wavFile);

    // ✅ ลบไฟล์เสียงหลังจากแปลงเสร็จ
    unlink($sourceFile);
    unlink($wavFile);

    return $text;
}
