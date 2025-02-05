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
    private $clientID;
    private $clientSecret;
    private $facebookToken;
    private $accessToken;
    private $debug = false;

    public function __construct($config)
    {
        $this->baseURL = 'https://graph.facebook.com/v21.0/';
        $this->clientID = $config['clientID'] ?? '';
        $this->clientSecret = $config['clientSecret'] ?? '';
        $this->facebookToken = $config['facebookToken'] ?? '';
        $this->accessToken = $config['accessToken'] ?? '';
        $this->http = new Client();
    }

    public function setDebug($value)
    {
        $this->debug = $value;
    }

    /*********************************************************************
     * 0. Access Token | เกี่ยวกับ Token
     */

    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function oauthAccessToken($redirectUri, $authCode)
    {
        try {

            $endPoint = $this->baseURL . 'oauth/access_token';

            // ส่งคำขอ POST ไปยัง API
            $response = $this->http->request('POST', $endPoint, [
                'form_params' => [
                    'client_id' => $this->clientID,
                    'client_secret' => $this->clientSecret,
                    'redirect_uri' => $redirectUri,
                    'code' => $authCode,
                ],
            ]);

            $responseData = json_decode($response->getBody());

            // ตรวจสอบสถานะ HTTP Code และข้อมูลใน Response
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200) {
                return $responseData;
            }

            // กรณีส่งข้อความล้มเหลว
            log_message('error', "Failed to get access token from facebook API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'FacebookClient::oauthAccessToken error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /*********************************************************************
     * 1. Message | ส่งข้อความ
     */

    public function pushMessage($to, $message_data, $message_type)
    {
        try {

            $text = [];

            if ($message_type == 'image') {

                

                $text = [
                    "attachment" => [
                        "type" => "image",
                        "payload" => [
                            "url" => $message_data,
                            "is_reusable" => true
                        ]
                    ]
                ];
            } else {
                $text = [
                    "text" => $message_data
                ];
            }

            $endPoint = $this->baseURL . 'me/messages';

            // กำหนดข้อมูล Body ที่จะส่งไปยัง API
            $data = [
                "messaging_type" => "RESPONSE",
                "recipient" => [
                    "id" => $to
                ],
                "message" => 
                   $text
                
            ];

            // ส่งคำขอ POST ไปยัง API
            $response = $this->http->request('POST', $endPoint, [
                'json' => $data, // ใช้ 'json' เพื่อแปลงข้อมูลให้อยู่ในรูปแบบ JSON
                'query' => [
                    'access_token' => $this->facebookToken,
                ],
            ]);

            $responseData = json_decode($response->getBody());

            // ตรวจสอบสถานะ HTTP Code และข้อมูลใน Response
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200 || isset($responseData->statusCode) && (int)$responseData->statusCode === 0) {
                return true; // ส่งข้อความสำเร็จ
            }

            // กรณีส่งข้อความล้มเหลว
            log_message('error', "Failed to send message to Facebook API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'FacebookClient::pushMessage error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /*********************************************************************
     * 2. Profile | ดึงข้อมูล
     */

    public function getUserProfileFacebook($UID)
    {
        try {

            $endPoint = $this->baseURL . $UID . '?fields=first_name,last_name,profile_pic&access_token=' . $this->facebookToken;

            $response = $this->http->request('GET', $endPoint);

            $responseData = json_decode($response->getBody());

            // ตรวจสอบสถานะ HTTP Code และข้อมูลใน Response
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200) {
                return $responseData;
            }

            // กรณีส่งข้อความล้มเหลว
            log_message('error', "Failed to get Profile from Facebook API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'FacebookClient::getUserProfileFacebook error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    public function getProfile()
    {
        try {

            $endPoint = $this->baseURL . 'me';

            $response = $this->http->request('GET', $endPoint, [
                'query' => [
                    'fields' => 'id,name,email,picture',
                    'access_token' => $this->accessToken,
                ],
            ]);

            $responseData = json_decode($response->getBody());

            // ตรวจสอบสถานะ HTTP Code และข้อมูลใน Response
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200) {
                return $responseData;
            }

            // กรณีส่งข้อความล้มเหลว
            log_message('error', "Failed to get Profile from Facebook API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'FacebookClient::getProfile error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /*********************************************************************
     * 3. Page | เกี่ยวกับเพจ
     */

    // ดึงรายชื่อเพจ
    public function getFbPagesList()
    {
        try {

            $endPoint = $this->baseURL . '/me/accounts';

            $response = $this->http->request('GET', $endPoint, [
                'query' => [
                    "access_token" => $this->accessToken
                ],
            ]);

            $responseData = json_decode($response->getBody());

            // ตรวจสอบสถานะ HTTP Code และข้อมูลใน Response
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200) {
                return $responseData;
            }

            // กรณีส่งข้อความล้มเหลว
            log_message('error', "Failed to get list page from Facebook API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'FacebookClient::getFbPagesList error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    // ดึงรูปเพจ
    public function getPicturePage($pageID)
    {
        try {

            $endPoint = $this->baseURL . $pageID . '/picture';

            $response = $this->http->request('GET', $endPoint, [
                'query' => [
                    'type' => 'large',
                    'redirect' => false,
                    "access_token" => $this->accessToken
                ],
            ]);

            $responseData = json_decode($response->getBody());

            // ตรวจสอบสถานะ HTTP Code และข้อมูลใน Response
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200) {
                return $responseData->data->url;
            }

            // กรณีส่งข้อความล้มเหลว
            log_message('error', "Failed to get picture from Facebook API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'FacebookClient::getPicturePage error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    // ผูกเพจเข้าไป App
    public function subscribedApps($pageID, $pageToken)
    {
        try {

            $endPoint = $this->baseURL . $pageID . '/subscribed_apps';

            $headers = [
                'Authorization' => "Bearer " . $pageToken,
            ];

            $data = [
                'subscribed_fields' => "messages",
                "messaging_postbacks",
                "messaging_optins",
                "message_deliveries",
                "message_reads",
                "message_reactio"
            ];

            $response = $this->http->request('POST', $endPoint, [
                'headers' => $headers,
                'json' => $data,
            ]);

            $responseData = json_decode($response->getBody());

            // ตรวจสอบสถานะ HTTP Code และข้อมูลใน Response
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200) {
                return true;
            }

            // กรณีส่งข้อความล้มเหลว
            log_message('error', "Failed to send Subscribed Apps from Facebook API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'FacebookClient::subscribedApps error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    // เช็คสถานะการผูก
    public function checkSubscribedApps($pageID)
    {
        try {

            $endPoint = $this->baseURL . $pageID . '/subscribed_apps';

            $response = $this->http->request('GET', $endPoint, [
                'query' => [
                    "access_token" => $this->accessToken
                ],
            ]);

            $responseData = json_decode($response->getBody());

            // ตรวจสอบสถานะ HTTP Code และข้อมูลใน Response
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200) {
                return $responseData;
            }

            // กรณีส่งข้อความล้มเหลว
            log_message('error', "Failed to check Subscribed Apps from Facebook API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'FacebookClient::checkSubscribedApps error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }
}
