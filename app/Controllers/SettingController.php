<?php

namespace App\Controllers;

use App\Integrations\Line\LineClient;
use App\Integrations\WhatsApp\WhatsAppClient;
use App\Models\UserSocialModel;

class SettingController extends BaseController
{
    private UserSocialModel $userSocialModel;

    public function __construct()
    {
        $this->userSocialModel = new UserSocialModel();
    }

    public function index()
    {
        $userID = $this->initializeSession();

        $userSocials = $this->userSocialModel->getUserSocialByUserID($userID);

        return view('/app', [
            'content' => 'setting/index',
            'title' => 'Chat',
            'css_critical' => '
                <link href="assets/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css">
                <link href="assets/libs/animate.css/animate.min.css" rel="stylesheet" type="text/css">
            ',
            'js_critical' => '
                <script src="assets/libs/sweetalert2/sweetalert2.min.js"></script>
                <script src="https://code.jquery.com/jquery-3.7.1.js" crossorigin="anonymous"></script>
                <script src="app/setting.js"></script>
            ',
            'user_socials' => $userSocials,
        ]);
    }

    public function setting()
    {
        $response = $this->handleResponse(function () {

            $userID = $this->initializeSession();

            $data = $this->getRequestData();

            return $this->processPlatformData($data->platform, $data, $userID);
        });

        return $response;
    }

    public function connection()
    {
        $response = $this->handleResponse(function () {

            $userID = $this->initializeSession();

            $data = $this->getRequestData();
            $userSocial = $this->userSocialModel->getUserSocialByID($data->userSocialID);

            $statusConnection = $this->processPlatformConnection($data->platform, $userSocial, $data->userSocialID);

            return [
                'success' => 1,
                'data' => $statusConnection,
                'message' => '',
            ];
        });

        return $response;
    }

    public function saveToken()
    {
        $response = $this->handleResponse(function () {

            $userID = $this->initializeSession();

            $data = $this->getRequestData();
            $this->updateToken($data->platform, $data);

            return ['success' => 1];
        });

        return $response;
    }

    public function removeSocial()
    {
        $response = $this->handleResponse(function () {

            $userID = $this->initializeSession();

            $data = $this->getRequestData();
            $userSocial = $this->userSocialModel->getUserSocialByID($data->userSocialID);

            if ($userSocial) {
                $this->userSocialModel->updateUserSocialByID($userSocial->id, [
                    'deleted_at' => date('Y-m-d H:i:s'),
                ]);

                return ['success' => 1, 'message' => 'ลบสำเร็จ'];
            }

            throw new \Exception('Social data not found');
        });

        return $response;
    }

    // -------------------------------------------------------------------------
    // Helper Functions
    // -------------------------------------------------------------------------

    private function initializeSession(): int
    {
        session()->set(['userID' => 1]);
        return session()->get('userID');
    }

    private function getRequestData(): object
    {
        $requestPayload = $this->request->getPost();
        return json_decode(json_encode($requestPayload));
    }

    private function handleResponse(callable $callback)
    {
        try {

            $response = $callback();

            return $this->response
                ->setStatusCode(200)
                ->setContentType('application/json')
                ->setJSON($response);
        } catch (\Exception $e) {
            return $this->response
                ->setStatusCode(500)
                ->setContentType('application/json')
                ->setJSON(['success' => 0, 'message' => $e->getMessage()]);
        }
    }

    private function processPlatformData(string $platform, object $data, int $userID): array
    {
        $tokenFields = $this->getTokenFields($platform);
        $insertData = $this->getInsertData($platform, $data, $userID);

        // ตรวจสอบว่ามีข้อมูลในระบบหรือยัง
        $isHaveToken = $this->userSocialModel->getUserSocialByPlatformAndToken($platform, $tokenFields);
        if ($isHaveToken) {
            return [
                'success' => 0,
                'message' => 'มีข้อมูลในระบบแล้ว',
            ];
        }

        // บันทึกข้อมูลลงฐานข้อมูล
        $userSocialID = $this->userSocialModel->insertUserSocial($insertData);

        return [
            'success' => 1,
            'message' => 'ข้อมูลถูกบันทึกเรียบร้อย',
            'data' => [],
            'userSocialID' => $userSocialID,
            'platform' => $platform
        ];
    }

    private function processPlatformConnection(string $platform, object $userSocial, int $userSocialID): string
    {
        $statusConnection = '0';

        switch ($platform) {
            case 'Facebook':
                if (!empty($userSocial->fb_token)) {
                    $statusConnection = '1';
                }
                break;

            case 'Line':
                $lineAPI = new LineClient([
                    'userSocialID' => $userSocial->id,
                    'accessToken' => $userSocial->line_channel_access_token,
                    'channelID' => $userSocial->line_channel_id,
                    'channelSecret' => $userSocial->line_channel_secret,
                ]);
                $accessToken = $lineAPI->accessToken();

                if ($accessToken) {
                    $statusConnection = '1';
                    $this->updateUserSocial($userSocialID, [
                        'line_channel_access_token' => $accessToken->access_token,
                    ]);
                }
                break;

            case 'WhatsApp':
                $whatsAppAPI = new WhatsAppClient([
                    'phoneNumberID' => $userSocial->whatsapp_phone_number_id,
                    'whatsAppToken' => $userSocial->whatsapp_token,
                ]);
                $phoneNumberID = $whatsAppAPI->getWhatsAppBusinessAccountIdForPhoneNumberID();

                if ($phoneNumberID) {
                    $statusConnection = '1';
                    $this->updateUserSocial($userSocialID, [
                        'whatsapp_phone_number_id' => $phoneNumberID,
                    ]);
                }
                break;
        }

        $this->updateUserSocial($userSocialID, [
            'is_connect' => $statusConnection,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $statusConnection;
    }

    private function updateUserSocial(int $userSocialID, array $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->userSocialModel->updateUserSocialByID($userSocialID, $data);
    }

    private function updateToken(string $platform, object $data)
    {
        $fields = match ($platform) {
            'Facebook' => ['fb_token' => $data->fbToken],
            'Line' => [], // Add Line token fields
            'WhatsApp' => [], // Add WhatsApp token fields
            default => throw new \Exception('Unsupported platform'),
        };

        if ($fields) {
            $this->updateUserSocial($data->userSocialID, $fields);
        }
    }
}
