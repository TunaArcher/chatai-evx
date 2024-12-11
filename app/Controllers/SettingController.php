<?php

namespace App\Controllers;

use App\Integrations\Line\LineClient;
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
        session()->set(['userID' => 1]);
        $userID = session()->get('userID');

        $user_socials = $this->userSocialModel->getUserSocialByUserID($userID);

        // ส่งข้อมูลไปยัง View
        return view('/app', [
            'content' => 'setting/index', // ชื่อไฟล์ View
            'title' => 'Chat', // ชื่อหน้า
            'css_critical' => '
                <link href="assets/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css">
                <link href="assets/libs/animate.css/animate.min.css" rel="stylesheet" type="text/css">
            ',
            'js_critical' => '
                <script src="assets/libs/sweetalert2/sweetalert2.min.js"></script>
                <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
                <script src="app/setting.js"></script>
            ', // ไฟล์ JS
            'user_socials' => $user_socials // ข้อมูลห้องสนทนา
        ]);
    }

    public function connection()
    {
        $response = [
            'success' => 0,
            'message' => '',
        ];
        $status = 500;

        try {
            session()->set(['userID' => 1]);
            $userID = session()->get('userID');

            $requestPayload = $this->request->getPost();
            $data = json_decode(json_encode($requestPayload));

            $platform = $data->platform;
            $userSocialID = $data->userSocialID;

            $userSocial = $this->userSocialModel->getUserSocialByID($userSocialID);

            $statusConnection = '0';

            switch ($platform) {
                case 'Facebook':

                    // TODO:: HANDLE
                    $statusConnection = '1';

                    $update = $this->userSocialModel->updateUserSocialByID($userSocialID, [
                        'is_connect' => $statusConnection,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    if ($update) {
                        $response['success'] = 1;
                        $response['data'] = $statusConnection;
                    }

                    break;

                case 'Line':

                    $lineAPI = new LineClient([
                        'userSocialID' => $userSocial->id,
                        'accessToken' => $userSocial->line_channel_access_token,
                        'channelID' => $userSocial->line_channel_id,
                        'channelSecret' => $userSocial->line_channel_secret,
                    ]);
                    $getAccessToken = $lineAPI->accessToken();

                    if ($getAccessToken) {

                        $statusConnection = '1';

                        $update = $this->userSocialModel->updateUserSocialByID($userSocialID, [
                            'line_channel_access_token' => $getAccessToken->access_token,
                            'is_connect' => $statusConnection,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);

                        $response['success'] = 1;
                        $response['data'] = $statusConnection;
                    }

                    break;

                case 'WhatsApp':

                    // TODO:: HANDLE
                    $statusConnection = '1';

                    $update = $this->userSocialModel->updateUserSocialByID($userSocialID, [
                        'is_connect' => $statusConnection,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    if ($update) {
                        $response['success'] = 1;
                        $response['data'] = $statusConnection;
                    }

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

    public function setting()
    {
        $response = [
            'success' => 0,
            'message' => '',
        ];
        $status = 500;

        try {
            session()->set(['userID' => 1]);
            $userID = session()->get('userID');

            $requestPayload = $this->request->getPost();
            $data = json_decode(json_encode($requestPayload));
            $platform = $data->btnradio;

            if (!$platform) {
                throw new \Exception('No platform specified');
            }

            $response = $this->processPlatformData($platform, $data, $userID);
            $status = 200;
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setJSON($response);
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
                return [
                    'fb_token' => $this->request->getPost('fb_token'),
                ];
            case 'Line':
                return [
                    'line_channel_id' => $this->request->getPost('line_channel_id'),
                    'line_channel_secret' => $this->request->getPost('line_channel_secret'),
                ];
            case 'WhatsApp':
                return [
                    'whatsapp_token' => $this->request->getPost('whatsapp_token'),
                    'whatsapp_phone_number_id' => $this->request->getPost('whatsapp_phone_number_id'),
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
            'name' => $data->{$platform . '_social_name'} ?? '',
        ];

        switch ($platform) {
            case 'Facebook':
                return array_merge($baseData, [
                    'fb_token' => $data->fb_token,
                ]);
            case 'Line':
                return array_merge($baseData, [
                    'line_channel_id' => $data->line_channel_id,
                    'line_channel_secret' => $data->line_channel_secret,
                ]);
            case 'WhatsApp':
                return array_merge($baseData, [
                    'whatsapp_token' => $data->whatsapp_token,
                    'whatsapp_phone_number_id' => $data->whatsapp_phone_number_id,
                ]);
            default:
                throw new \Exception('Unsupported platform');
        }
    }
}
