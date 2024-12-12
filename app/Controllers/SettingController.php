<?php

namespace App\Controllers;

use App\Integrations\Line\LineClient;
use App\Integrations\WhatsApp\WhatsAppClient;
use App\Models\MessageRoomModel;
use App\Models\UserSocialModel;

class SettingController extends BaseController
{
    private MessageRoomModel $messageRoomModel;
    private UserSocialModel $userSocialModel;

    public function __construct()
    {
        $this->messageRoomModel = new MessageRoomModel();
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

    public function removeSocial()
    {
        $response = $this->handleResponse(function () {

            $userID = $this->initializeSession();

            // $data = $this->getRequestData();
            $data = $this->request->getJSON();
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

    public function saveToken()
    {
        $response = [
            'success' => 0,
            'message' => '',
        ];
        $status = 500;

        try {
            session()->set(['userID' => 1]);
            $userID = session()->get('userID');

            $data = $this->request->getJSON();

            // $platform = $data->platform;
            $platform = 'Facebook';
            $userSocialID = $data->userSocialID;

            $userSocial = $this->userSocialModel->getUserSocialByID($userSocialID);

            switch ($platform) {
                case 'Facebook':

                    $this->userSocialModel->updateUserSocialByID($userSocialID, [
                        'fb_token' => $data->fbToken,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    $response['success'] = 1;

                    break;

                case 'Line':
                    break;
                case 'WhatsApp':
                    break;
                case 'Instagram':
                    break;
                case 'Tiktok':
                    break;
            }

            $status = 200;
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setJSON($response);
    }


    public function settingAI()
    {
        $response = $this->handleResponse(function () {

            $userID = $this->initializeSession();

            // $data = $this->getRequestData();
            $data = $this->request->getJSON();
            $userSocialID = $data->userSocialID;
            $userSocial = $this->userSocialModel->getUserSocialByID($userSocialID);

            if ($userSocial) {

                $oldStatus = $userSocial->ai;
                $newStatus = $userSocial->ai === 'on' ? 'off' : 'on';

                $this->userSocialModel->updateUserSocialByID($userSocial->id, [
                    'ai' => $newStatus,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                log_message('info', "ปรับการใช้งาน Social User ID $userSocial->id จาก $oldStatus เป็น $newStatus ");
            }

            return [
                'success' => 1,
                'message' => 'สำเร็จ',
                'data' => [
                    'oldStatus' => $oldStatus,
                    'newStatus' => $newStatus,
                ]
            ];

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

    private function getTokenFields(string $platform): array
    {
        switch ($platform) {
            case 'Facebook':
            case 'Line':
                return [
                    'line_channel_id' => $this->request->getPost('line_channel_id'),
                    'line_channel_secret' => $this->request->getPost('line_channel_secret'),
                ];
            case 'WhatsApp':
                return [
                    'whatsapp_token' => $this->request->getPost('whatsapp_token'),
                    // 'whatsapp_phone_number_id' => $this->request->getPost('whatsapp_phone_number_id'),
                ];
            case 'Instagram':
                return [
                    'ig_token' => $this->request->getPost('instagram_token'),
                ];
            case 'Tiktok':
                return [
                    'tiktok_token' => $this->request->getPost('tiktok_token'),
                ];
            default:
                return [];
        }
    }

    private function getInsertData(string $platform, object $data, int $userID): array
    {
        $baseData = [
            'user_id' => $userID,
            'platform' => $platform,
            'name' => $data->{mb_strtolower($platform) . '_social_name'} ?? '',
        ];

        switch ($platform) {
            case 'Facebook':
                return $baseData;
            case 'Line':
                return array_merge($baseData, [
                    'line_channel_id' => $data->line_channel_id,
                    'line_channel_secret' => $data->line_channel_secret,
                ]);
            case 'WhatsApp':
                return array_merge($baseData, [
                    'whatsapp_token' => $data->whatsapp_token,
                    // 'whatsapp_phone_number_id' => $data->whatsapp_phone_number_id,
                ]);
            case 'Instagram':
                return array_merge($baseData, [
                    'ig_token' => $data->instagram_token,
                ]);
            case 'Tiktok':
                return array_merge($baseData, [
                    'tiktok_token' => $data->tiktok_token,

                ]);
            default:
                throw new \Exception('Unsupported platform');
        }
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

            case 'Instagram':
                // TODO:: HANDLE CHECK
                if (!empty($userSocial->ig_token)) {
                    $statusConnection = '1';
                }
                break;

            case 'Tiktok':
                // TODO:: HANDLE CHECK
                if (!empty($userSocial->tiktok_token)) {
                    $statusConnection = '1';
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
}
