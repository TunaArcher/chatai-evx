<?php

namespace App\Integrations\WhatsApp;

use \GuzzleHttp\Client;
use \GuzzleHttp\Handler\CurlHandler;
use \GuzzleHttp\HandlerStack;
use \GuzzleHttp\Middleware;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

class TiktokClient
{
    private $http;
    private $baseURL;
    private $apiKey;
    private $accountKey;
    private $accessToken;
    private $debug = false;

    public function __construct($config)
    {
        $this->apiKey = $config['apiKey'] ?? '';
        $this->accountKey = $config['accountKey'] ?? '';
        $this->accessToken = $config['tiktokToken'] ?? '';
        $this->http = new Client();
    }

    public function setDebug($value)
    {
        $this->debug = $value;
    }

    /*********************************************************************
     * 1. Message | ส่งข้อความ
     */

    public function pushMessage($to, $messages, $conversationID, $conversationShortID, $ticket)
    {
        try {

            $endPoint = 'https://sandbox.tikapi.io/user/message/send';

            $headers = [
                'X-API-KEY' => $this->apiKey,
                'X-ACCOUNT-KEY' => $this->accountKey
            ];

            // กำหนดข้อมูล Body ที่จะส่งไปยัง API
            $data = [
                'text' => $messages,
                'conversation_id' => $conversationID,
                'conversation_short_id' => $conversationShortID,
                'ticket' => $ticket,
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
            log_message('error', "Failed to send message to Tiktok API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'TiktokClient::pushMessage error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    public function getConversations()
    {
        try {

            $endPoint = 'https://sandbox.tikapi.io/user/conversations';

            $headers = [
                'X-API-KEY' => $this->apiKey,
                'X-ACCOUNT-KEY' => $this->accountKey
            ];

            // ส่งคำขอ GET ไปยัง API
            $response = $this->http->request('GET', $endPoint, [
                'headers' => $headers
            ]);

            // แปลง Response กลับมาเป็น Object
            $responseData = json_decode($response->getBody());

            // ตรวจสอบสถานะ HTTP Code และข้อมูลใน Response
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200) {
                return $responseData;
            }

            // กรณีส่งข้อความล้มเหลว
            log_message('error', "Failed to get conversations Tiktok API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'TiktokClient::getConversations error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    public function getMessage($conversationID, $conversationShortID)
    {
        try {

            $endPoint = 'https://sandbox.tikapi.io/user/messages';

            $headers = [
                'X-API-KEY' => $this->apiKey,
                'X-ACCOUNT-KEY' => $this->accountKey
            ];

            // ส่งคำขอ GET ไปยัง API
            $response = $this->http->request('GET', $endPoint, [
                'headers' => $headers,
                'query' => [
                    'conversation_id' => $conversationID,
                    'conversation_short_id' => $conversationShortID,
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
            log_message('error', "Failed to get message Tiktok API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'TiktokClient::getMessage error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /*********************************************************************
     * 1. Profile | ดึงข้อมูล
     */

    public function getUserProfile($UID)
    {
        try {

            $endPoint = 'https://sandbox.tikapi.io/user/info';

            $headers = [
                'X-API-KEY' => $this->apiKey,
                'X-ACCOUNT-KEY' => $this->accountKey
            ];

            // ส่งคำขอ GET ไปยัง API
            $response = $this->http->request('GET', $endPoint, [
                'headers' => $headers,
                'query' => [
                    'username' => $UID,
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
            log_message('error', "Failed to send message to WhatsApp API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'TiktokClient::getProfile error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }
}
