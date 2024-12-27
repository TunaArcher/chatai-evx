<?php

namespace App\Integrations\WhatsApp;

use \GuzzleHttp\Client;
use \GuzzleHttp\Handler\CurlHandler;
use \GuzzleHttp\HandlerStack;
use \GuzzleHttp\Middleware;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

class WhatsAppClient
{
    private $http;
    private $baseURL;
    private $phoneNumberID;
    private $accessToken;
    private $debug = false;

    public function __construct($config)
    {
        $this->baseURL = 'https://graph.facebook.com/v21.0/';
        $this->phoneNumberID = $config['phoneNumberID'] ?? '';
        $this->accessToken = $config['whatsAppToken'] ?? '';
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
                'Authorization' => "Bearer " . $this->accessToken,
                'Content-Type' => 'application/json',
            ];

            $data = [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'text',
                'text' => [
                    'body' => $messages
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

    public function getUserProfile($UID)
    {
        try {

            $endPoint = $this->baseURL . $UID . '/phone_numbers/';

            $headers = [
                'Authorization' => "Bearer " . $this->accessToken,
            ];

            $response = $this->http->request('GET', $endPoint, [
                'headers' => $headers
            ]);

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

    public function getBussinessID()
    {
        try {

            $endPoint = $this->baseURL . '/me/businesses/';

            $response = $this->http->request('GET', $endPoint, [
                'query' => [
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
            log_message('error', "Failed to get bussiness id from WhatsApp API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'WhatsAppAPI::getBussinessID error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /*********************************************************************
     * 1. Get Phone Number ID | ดึง Phone ID ใช้ในการ Request
     */

    public function getWhatsAppBusinessAccountId()
    {
        try {

            $endPoint = $this->baseURL . '/me/';

            // เรียก API เพื่อดึง WhatsApp Business Account ID
            $response = $this->http->request('GET', $endPoint, [
                'query' => [
                    'fields' => 'id,name,accounts',
                    'access_token' => $this->accessToken,
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            if (isset($data['accounts']['data'][0]['whatsapp_business_account']['id'])) {
                return $data['accounts']['data'][0]['whatsapp_business_account']['id'];
            } else {
                // กรณีส่งข้อความล้มเหลว
                log_message('error', "Failed to get WhatsAppBusiness Account ID to WhatsApp API: " . json_encode($data));
                return false;
            }
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'WhatsAppAPI::getWhatsAppBusinessAccountId error {message}', ['message' => $e->getMessage()]);
            return null;
        }
    }

    public function getPhoneNumberId($whatsappBusinessAccountId)
    {
        try {

            $endPoint = $this->baseURL . $whatsappBusinessAccountId . '/phone_numbers/';

            // เรียก API เพื่อดึง Phone Number ID
            $response = $this->http->request('GET', $endPoint, [
                'query' => [
                    'access_token' => $this->accessToken,
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            if (isset($data['data'][0]['id'])) {
                return $data['data'][0]['id'];
            } else {
                log_message('error', "Failed to get PhoneNumber ID to WhatsApp API: " . json_encode($data));
                throw new Exception("ไม่พบ Phone Number ID");
            }
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'WhatsAppAPI::getPhoneNumberId error {message}', ['message' => $e->getMessage()]);
            return null;
        }
    }

    public function getPhoneNumber($whatsappBusinessAccountId)
    {
        try {

            $endPoint = $this->baseURL . $whatsappBusinessAccountId . '/phone_numbers/';

            // เรียก API เพื่อดึง Phone Number ID
            $response = $$this->http->request('GET', $endPoint, [
                'query' => [
                    'access_token' => $this->accessToken,
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            if (isset($data['data'][0])) {
                return $data['data'][0];
            } else {
                log_message('error', "Failed to get PhoneNumber to WhatsApp API: " . json_encode($data));
                throw new Exception("ไม่พบ Phone Number ID");
            }
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'WhatsAppAPI::getPhoneNumber error {message}', ['message' => $e->getMessage()]);
            return null;
        }
    }

    public function getWhatsAppBusinessAccountIdForPhoneNumberID()
    {

        $whatsappBusinessAccountId = $this->getWhatsAppBusinessAccountId();

        if ($whatsappBusinessAccountId) {

            // ดึง Phone Number ID
            $phoneNumberId = $this->getPhoneNumberId($whatsappBusinessAccountId);

            if ($phoneNumberId) return $phoneNumberId;
        }
    }

    /*********************************************************************
     * 3. Account | ดึงข้อมูลเกี่ยวกับ Account
     */

    // ดึงรายชื่อเพจ
    public function getListBusinessAccounts($businessId)
    {
        try {

            // $endPoint = $this->baseURL . $businessId . '/whatsapp_business_accounts';
            $endPoint = $this->baseURL . $businessId . '/owned_whatsapp_business_accounts';

            // $headers = [
            //     'Authorization' => "Bearer " . $this->facebookToken,
            // ];

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
            log_message('error', "Failed to get list Business Accounts from WhatsApp API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'WhatsAppClient::getListBusinessAccounts error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    // ผูกเพจเข้าไป App
    public function subscribedApps($WABID)
    {
        try {

            $endPoint = $this->baseURL . $WABID . '/subscribed_apps';

            $headers = [
                'Authorization' => "Bearer " . $this->accessToken,
            ];

            $response = $this->http->request('POST', $endPoint, [
                'headers' => $headers
            ]);

            $responseData = json_decode($response->getBody());

            // ตรวจสอบสถานะ HTTP Code และข้อมูลใน Response
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200) {
                return true;
            }

            // กรณีส่งข้อความล้มเหลว
            log_message('error', "Failed to send Subscribed Apps from WhatsApp API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'WhatsAppClient::subscribedApps error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }
}
