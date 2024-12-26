<?php

namespace App\Integrations\Instagram;

use \GuzzleHttp\Client;
use \GuzzleHttp\Handler\CurlHandler;
use \GuzzleHttp\HandlerStack;
use \GuzzleHttp\Middleware;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

class InstagramClient
{
    private $http;
    private $baseURL;
    private $accessToken;
    private $debug = false;

    public function __construct($config)
    {
        $this->baseURL = 'https://graph.facebook.com/v21.0/';
        $this->accessToken = $config['accessToken'] ?? '';
        $this->http = new Client();
    }

    public function setDebug($value)
    {
        $this->debug = $value;
    }

    /*********************************************************************
     * 1. Message | ส่งข้อความ
     */

    public function pushMessage($to, $messages)
    {
        try {

            $endPoint = $this->baseURL . '/messages/';

            $headers = [
                'Authorization' => "Bearer " . $this->accessToken,
                'Content-Type' => 'application/json',
            ];

            // กำหนดข้อมูล Body ที่จะส่งไปยัง API
            $data = [
                'recipient' => $to,
                'message' => [
                    'text' => $messages,
                ],
            ];

            // ส่งคำขอ POST ไปยัง API
            $response = $this->http->request('POST', $endPoint, [
                'headers' => $headers,
                'json' => $data, // ใช้ 'json' เพื่อแปลงข้อมูลให้อยู่ในรูปแบบ JSON
            ]);

            // แปลง Response กลับมาเป็น Object
            $responseData = json_decode($response->getBody());

            // ตรวจสอบสถานะ HTTP Code และข้อมูลใน Response
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200 || isset($responseData->statusCode) && (int)$responseData->statusCode === 0) {
                return true; // ส่งข้อความสำเร็จ
            }

            // กรณีส่งข้อความล้มเหลว
            log_message('error', "Failed to send message to Instagram API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'InstagramClient::pushMessage error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /*********************************************************************
     * 1. Profile | ดึงข้อมูล
     */

    public function getUserProfile($UID)
    {
        try {

            $endPoint = $this->baseURL . $UID;

            // ส่งคำขอ GET ไปยัง API
            $response = $this->http->request('GET', $endPoint, [
                'query' => [
                    'access_token' => $this->accessToken,
                    'fields' => 'id, name, profile_picture_url',
                ]
            ]);

            // แปลง Response กลับมาเป็น Object
            $responseData = json_decode($response->getBody());

            // ตรวจสอบสถานะ HTTP Code และข้อมูลใน Response
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200) {
                return $responseData;
            }

            // กรณีส่งข้อความล้มเหลว
            log_message('error', "Failed to profile from Instagram API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'InstagramClient::getProfile error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /*********************************************************************
     * 3. Account | ดึงข้อมูลเกี่ยวกับ Account
     */

    // ดึงรายชื่อเพจ
    public function getListBusinessAccounts()
    {
        try {

            // $endPoint = $this->baseURL . $businessId . '/whatsapp_business_accounts';
            $endPoint = $this->baseURL . 'me/accounts';

            // ส่งคำขอ GET ไปยัง API
            $response = $this->http->request('GET', $endPoint, [
                'query' => [
                    "fields" => 'id,name,instagram_business_account,picture',
                    "access_token" => $this->accessToken
                ],
            ]);

            // แปลง Response กลับมาเป็น Object
            $responseData = json_decode($response->getBody());

            // ตรวจสอบสถานะ HTTP Code และข้อมูลใน Response
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200) {
                return $responseData;
            }

            // กรณีส่งข้อความล้มเหลว
            log_message('error', "Failed to get list Business Accounts from Instagram API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'InstagramClient::getListBusinessAccounts error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    // ผูกเพจเข้าไป App
    public function subscribedApps($instagramBusinessAccountID)
    {
        try {

            $endPoint = $this->baseURL . getenv('APP_ID') . '/subscriptions';

            $headers = [
                'Authorization' => "Bearer " . getenv('APP_ID') . '|' . getenv('APP_SECRET'),
                'Content-Type' => 'application/json',
            ];

            // กำหนดข้อมูล Body ที่จะส่งไปยัง API
            // $data = [
            //     "object" => "instagram",
            //     "fields" => ["messages"],
            //     "callback_url" => base_url('/webhook'),
            //     "verify_token" => "HAPPY",
            //     "access_token" => getenv('APP_ID') . '|' . getenv('APP_SECRET')
            // ];
            
            $data = [
                "object" => "page",
                "fields" => "messages",
                "callback_url" => base_url('/webhook'),
                "verify_token" => "HAPPY",
            ];

            // ส่งคำขอ GET ไปยัง API
            $response = $this->http->request('POST', $endPoint, [
                'headers' => $headers,
                'json' => $data, // ใช้ 'json' เพื่อแปลงข้อมูลให้อยู่ในรูปแบบ JSON
            ]);

            // แปลง Response กลับมาเป็น Object
            $responseData = json_decode($response->getBody());

            // ตรวจสอบสถานะ HTTP Code และข้อมูลใน Response
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200) {
                return true;
            }

            // กรณีส่งข้อความล้มเหลว
            log_message('error', "Failed to send Subscribed Apps from Instagram API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'InstagramClient::subscribedApps error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }
}
