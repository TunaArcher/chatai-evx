<?php

namespace App\Libraries;

use \GuzzleHttp\Client;
use \GuzzleHttp\Handler\CurlHandler;
use \GuzzleHttp\HandlerStack;
use \GuzzleHttp\Middleware;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

class WhatsApp
{
    private $http;
    private $baseURL;
    private $phoneNumberID;
    private $whatsAppToken;
    private $debug = false;

    public function __construct($config)
    {
        $this->baseURL = 'https://graph.facebook.com/v21.0/';
        $this->phoneNumberID = $config['phoneNumberID'];
        $this->whatsAppToken = $config['whatsAppToken'];
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

            $endPoint = $this->baseURL . $this->phoneNumberID . '/messages/';

            $headers = [
                'Authorization' => "Bearer " . $this->whatsAppToken,
                'Content-Type' => 'application/json',
            ];

            // กำหนดข้อมูล Body ที่จะส่งไปยัง API
            $data = [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'text',
                'text' => [
                    [
                        'body' => $messages
                    ],
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
            log_message('error', "Failed to send message to WhatsApp API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'WhatsAppAPI::pushMessage error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /*********************************************************************
     * 1. Profile | ดึงข้อมูล
     */

    public function getProfile($UID)
    {
        try {

            $endPoint = $this->baseURL . $UID . '/phone_numbers/';

            $headers = [
                'Authorization' => "Bearer " . $this->whatsAppToken,
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
            log_message('error', "Failed to send message to WhatsApp API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'WhatsAppAPI::getProfile error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }
}
