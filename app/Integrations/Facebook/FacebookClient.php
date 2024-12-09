<?php

namespace App\Integrations\Facebook;

use \GuzzleHttp\Client;
use \GuzzleHttp\Handler\CurlHandler;
use \GuzzleHttp\HandlerStack;
use \GuzzleHttp\Middleware;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

class FacebookClient
{
    private $http;
    private $baseURL;
    private $facebookToken;
    private $debug = false;

    public function __construct($config)
    {
        $this->baseURL = 'https://graph.facebook.com/';
        $this->facebookToken = $config['facebookToken'];
        $this->http = new Client();
    }

    public function setDebug($value)
    {
        $this->debug = $value;
    }

    /*********************************************************************
     * 1. Message | ส่งข้อความ
     */

    public function pushMessage($to, $text)
    {
        try {

            $endPoint = $this->baseURL . 'v21.0/me/messages';

            // $headers = [
            //     'Authorization' => "Bearer " . $this->facebookToken,
            //     'Content-Type' => 'application/json',
            // ];

            // กำหนดข้อมูล Body ที่จะส่งไปยัง API
            $data = [
                "messaging_type" => "RESPONSE",
                "recipient" => [
                    "id" => $to
                ],
                "message" => [
                    "text" => $text
                ]
            ];

            // ส่งคำขอ POST ไปยัง API
            $response = $this->http->request('POST', $endPoint, [
                'json' => $data, // ใช้ 'json' เพื่อแปลงข้อมูลให้อยู่ในรูปแบบ JSON
                'query' => [
                    'access_token' => $this->facebookToken,
                ],
            ]);

            // แปลง Response กลับมาเป็น Object
            $responseData = json_decode($response->getBody());

            // ตรวจสอบสถานะ HTTP Code และข้อมูลใน Response
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200 || isset($responseData->statusCode) && (int)$responseData->statusCode === 0) {
                return true; // ส่งข้อความสำเร็จ
            }

            // กรณีส่งข้อความล้มเหลว
            log_message('error', "Failed to send message to facebook API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'facebook API::pushMessage error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /*********************************************************************
     * 1. Profile | ดึงข้อมูล
     */

    public function getUserProfileFacebook($UID)
    {
        try {

            $endPoint = $this->baseURL . $UID . '?fields=first_name,last_name,profile_pic&access_token=' . $this->facebookToken;

            // $headers = [
            //     'Authorization' => "Bearer " . $this->facebookToken,
            // ];

            // ส่งคำขอ GET ไปยัง API
            $response = $this->http->request('GET', $endPoint);

            // แปลง Response กลับมาเป็น Object
            $responseData = json_decode($response->getBody());

            // ตรวจสอบสถานะ HTTP Code และข้อมูลใน Response
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200) {
                return $responseData;
            }

            // กรณีส่งข้อความล้มเหลว
            log_message('error', "Failed to send message to Facebook API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'FacebookAPI::getProfile error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }
}
