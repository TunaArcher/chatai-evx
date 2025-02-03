<?php

namespace App\Integrations\Instagram;

use \GuzzleHttp\Client;

class InstagramClient
{
    private $http;
    private $baseURL;
    private $clientID;
    private $clientSecret;
    private $accessToken;
    private $debug = false;

    public function __construct($config)
    {
        $this->baseURL = 'https://graph.instagram.com/v21.0/';
        $this->clientID = $config['clientID'] ?? '';
        $this->clientSecret = $config['clientSecret'] ?? '';
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

            $endPoint = 'https://api.instagram.com/oauth/access_token';

            $response = $this->http->request('POST', $endPoint, [
                'form_params' => [
                    'client_id' => $this->clientID,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'authorization_code',
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
            log_message('error', "Failed to get access token from Instagram API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'InstagramClient::oauthAccessToken error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    public function getLongAccessToken($shortAccessToken)
    {
        try {

            $endPoint = 'https://graph.instagram.com/access_token';

            $response = $this->http->request('GET', $endPoint, [
                'query' => [
                    'grant_type' => 'ig_exchange_token',
                    'client_secret' => $this->clientSecret,
                    'access_token' => $shortAccessToken,
                ],
            ]);

            $responseData = json_decode($response->getBody());

            // ตรวจสอบสถานะ HTTP Code และข้อมูลใน Response
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200) {
                return $responseData->access_token;
            }

            // กรณีส่งข้อความล้มเหลว
            log_message('error', "Failed to get LongAccessToken from Instagram API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'InstagramClient::getLongAccessToken error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    public function getRefreshToken($longAccessToken)
    {
        try {

            $endPoint = 'https://graph.instagram.com/refresh_access_token';

            $response = $this->http->request('POST', $endPoint, [
                'query' => [
                    'grant_type' => 'ig_refresh_token',
                    'access_token' => $longAccessToken,
                ],
            ]);

            $responseData = json_decode($response->getBody());

            // ตรวจสอบสถานะ HTTP Code และข้อมูลใน Response
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200) {
                return $responseData->access_token;
            }

            // กรณีส่งข้อความล้มเหลว
            log_message('error', "Failed to get RefreshToken from Instagram API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'InstagramClient::getRefreshToken error {message}', ['message' => $e->getMessage()]);
            return false;
        }
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

            $data = [
                'recipient' => $to,
                'message' => [
                    'text' => $messages,
                ],
            ];

            $response = $this->http->request('POST', $endPoint, [
                'headers' => $headers,
                'json' => $data, 
            ]);

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

    // /*********************************************************************
    //  * 3. Account | ดึงข้อมูลเกี่ยวกับ Account
    //  */

    // // ดึงรายชื่อเพจ
    // public function getListBusinessAccounts()
    // {
    //     try {

    //         // $endPoint = $this->baseURL . $businessId . '/whatsapp_business_accounts';
    //         $endPoint = $this->baseURL . 'me/accounts';

    //         // ส่งคำขอ GET ไปยัง API
    //         $response = $this->http->request('GET', $endPoint, [
    //             'query' => [
    //                 "fields" => 'id,name,instagram_business_account,picture',
    //                 "access_token" => $this->accessToken
    //             ],
    //         ]);

    //     
    //         $responseData = json_decode($response->getBody());

    //         // ตรวจสอบสถานะ HTTP Code และข้อมูลใน Response
    //         $statusCode = $response->getStatusCode();
    //         if ($statusCode === 200) {
    //             return $responseData;
    //         }

    //         // กรณีส่งข้อความล้มเหลว
    //         log_message('error', "Failed to get list Business Accounts from Instagram API: " . json_encode($responseData));
    //         return false;
    //     } catch (\Exception $e) {
    //         // จัดการข้อผิดพลาด
    //         log_message('error', 'InstagramClient::getListBusinessAccounts error {message}', ['message' => $e->getMessage()]);
    //         return false;
    //     }
    // }

    // // ผูกเพจเข้าไป App
    // public function subscribedApps($instagramBusinessAccountID)
    // {
    //     try {

    //         $endPoint = $this->baseURL . getenv('APP_ID') . '/subscriptions';

    //         $headers = [
    //             'Authorization' => "Bearer " . getenv('APP_ID') . '|' . getenv('APP_SECRET'),
    //             'Content-Type' => 'application/json',
    //         ];

    //     
    //         // $data = [
    //         //     "object" => "instagram",
    //         //     "fields" => ["messages"],
    //         //     "callback_url" => base_url('/webhook'),
    //         //     "verify_token" => "HAPPY",
    //         //     "access_token" => getenv('APP_ID') . '|' . getenv('APP_SECRET')
    //         // ];

    //         $data = [
    //             "object" => "page",
    //             "fields" => "messages",
    //             "callback_url" => base_url('/webhook'),
    //             "verify_token" => "HAPPY",
    //         ];

    //         // ส่งคำขอ GET ไปยัง API
    //         $response = $this->http->request('POST', $endPoint, [
    //             'headers' => $headers,
    //             'json' => $data, 
    //         ]);

    //     
    //         $responseData = json_decode($response->getBody());

    //         // ตรวจสอบสถานะ HTTP Code และข้อมูลใน Response
    //         $statusCode = $response->getStatusCode();
    //         if ($statusCode === 200) {
    //             return true;
    //         }

    //         // กรณีส่งข้อความล้มเหลว
    //         log_message('error', "Failed to send Subscribed Apps from Instagram API: " . json_encode($responseData));
    //         return false;
    //     } catch (\Exception $e) {
    //         // จัดการข้อผิดพลาด
    //         log_message('error', 'InstagramClient::subscribedApps error {message}', ['message' => $e->getMessage()]);
    //         return false;
    //     }
    // }

    public function subscribedApps($userID)
    {
        try {

            $endPoint = $this->baseURL . $userID . '/subscribed_apps';

            // ส่งคำขอ GET ไปยัง API
            $response = $this->http->request('POST', $endPoint, [
                'query' => [
                    'subscribed_fields' => 'messages',
                    'access_token' => $this->accessToken,
                ]
            ]);

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
