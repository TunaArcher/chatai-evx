<?php

namespace App\Integrations\Line;

use App\Models\UserSocialModel;
use \GuzzleHttp\Client;
use \GuzzleHttp\Handler\CurlHandler;
use \GuzzleHttp\HandlerStack;
use \GuzzleHttp\Middleware;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

class LineClient
{
    private $http;
    private $baseURL;
    private $userSocialID;
    private $accessToken;
    private $channelID;
    private $channelSecret;
    private $debug = false;

    public function __construct($config = [])
    {
        $this->baseURL = 'https://api.line.me/v2';

        $this->userSocialID = $config['userSocialID'] ?? '';

        $this->accessToken = $config['accessToken'] ?? '';

        $this->channelID = $config['channelID'] ?? '';

        $this->channelSecret = $config['channelSecret'] ?? '';

        $stack = new HandlerStack();

        $stack->setHandler(new CurlHandler());

        $stack->push(Middleware::mapRequest(function (RequestInterface $Request) {
            $request = $Request;

            $this->lastRequest = $request;

            return $request;
        }));

        $stack->push(Middleware::mapResponse(function (ResponseInterface $Response) {

            $statusCode = $Response->getStatusCode();

            if ($statusCode === 401) {

                $getAccessToken = $this->accessToken();

                if ($getAccessToken) {

                    $userSocialModel = new UserSocialModel();
                    $userSocialModel->updateUserSocialByID($this->userSocialID, [
                        'line_channel_access_token' => $getAccessToken->access_token,
                        'is_connect' => '1',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    $lastRequest = $this->lastRequest;

                    $url = (string) $lastRequest->getUri();
                    $method = (string) $lastRequest->getMethod();
                    // $header = $lastRequest->getHeaders();
                    $body = $lastRequest->getBody();

                    $response = $this->http->request($method, $url, [
                        'headers' => [
                            'Authorization' => "Bearer " . $this->accessToken
                        ],
                        'body' => $body,
                    ]);

                    return $response;
                } else {
                    $userSocialModel = new UserSocialModel();
                    $userSocialModel->updateUserSocialByID($this->userSocialID, [
                        'is_connect' => '0',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            return $Response;
        }));

        $option = ['handler' => $stack];

        $this->http = new Client($option);
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

            $endPoint = $this->baseURL . '/bot/message/push/';

            $headers = [
                'Authorization' => "Bearer " . $this->accessToken,
                'Content-Type' => 'application/json',
            ];

            // กำหนดข้อมูล Body ที่จะส่งไปยัง API
            $data = [
                'to' => $to,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $messages
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
            log_message('error', "Failed to send message to Line API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'LineClient::pushMessage error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /*********************************************************************
     * 1. Profile | ดึงข้อมูล
     */

    public function getUserProfile($UID)
    {
        try {

            $endPoint = $this->baseURL . '/bot/profile/' . $UID;

            $headers = [
                'Authorization' => "Bearer " . $this->accessToken,
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
            log_message('error', "Failed to get profile from Line API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'LineClient::getProfile error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /*********************************************************************
     * 1. Access Token | ดึง Token
     * ใช้สำหรับเพื่อที่จะไม่ให้ยูสเซอร์ยุ่งยาก ไปเอา Token มาใส่ในระบบเอง เราสามารถทำเป็น Auto โดยส่ง Channel ID กับ Channel Secret ไป
     * แต่มีอายุใช้งานแค่ 30 วัน
     * จึงใช้ Middleware ว่าถ้าหาก Token หมดอายุ ให้ AccessToekn() อีกครั้ง
     */

    public function accessToken()
    {
        try {

            $endPoint = $this->baseURL . '/oauth/accessToken';

            // ส่งคำขอ POST ไปยัง API
            $response = $this->http->request('POST', $endPoint, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->channelID,
                    'client_secret' => $this->channelSecret,
                ]
            ]);

            // แปลง Response กลับมาเป็น Object
            $responseData = json_decode($response->getBody());

            // ตรวจสอบสถานะ HTTP Code และข้อมูลใน Response
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200 || isset($responseData->statusCode)) {
                return $responseData;
            }

            // กรณีส่งข้อความล้มเหลว
            log_message('error', "Failed to get access token from Line API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'LineClient::accessToken error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }
}
