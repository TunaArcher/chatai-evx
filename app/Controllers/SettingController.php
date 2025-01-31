<?php

namespace App\Controllers;

use App\Integrations\Line\LineClient;
use App\Integrations\WhatsApp\WhatsAppClient;
use App\Models\CustomerModel;
use App\Models\MessageRoomModel;
use App\Models\UserModel;
use App\Models\UserSocialModel;
use App\Libraries\ChatGPT;
use Aws\S3\S3Client;


class SettingController extends BaseController
{
    private UserSocialModel $userSocialModel;
    private CustomerModel $customerModel;
    private UserModel $userModel;
    private $s3_bucket;
    private $s3_secret_key;
    private $s3_key;
    private $s3_endpoint;
    private $s3_region;
    private $s3_cdn_img;
    private $s3Client;

    public function __construct()
    {
        $this->userSocialModel = new UserSocialModel();
        $this->customerModel = new CustomerModel();
        $this->userModel = new UserModel();


        $this->s3_bucket = getenv('S3_BUCKET');
        $this->s3_secret_key = getenv('SECRET_KEY');
        $this->s3_key = getenv('KEY');
        $this->s3_endpoint = getenv('ENDPOINT');
        $this->s3_region = getenv('REGION');
        $this->s3_cdn_img = getenv('CDN_IMG');

        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region'  => $this->s3_region,
            'endpoint' => $this->s3_endpoint,
            'use_path_style_endpoint' => false,
            'credentials' => [
                'key'    => $this->s3_key,
                'secret' => $this->s3_secret_key
            ],
            'suppress_php_deprecation_warning' => true, // ปิดข้อความเตือน
        ]);
    }

    public function index()
    {
        $userID = $this->initializeSession();

        $userSocials = $this->userSocialModel->getUserSocialByUserID($userID);

        return view('/app', [
            'content' => 'setting/connect',
            'title' => 'Chat',
            'css_critical' => '
                <link href="' . base_url('assets/libs/sweetalert2/sweetalert2.min.css') . '" rel="stylesheet" type="text/css">
                <link href="' . base_url('assets/libs/animate.css/animate.min.css') . '" rel="stylesheet" type="text/css">
            ',
            'js_critical' => '
                <script src="https://code.jquery.com/jquery-3.7.1.js" crossorigin="anonymous"></script>
                <script src="' . base_url('assets/libs/sweetalert2/sweetalert2.min.js') . '"></script>       
                <script src="' . base_url('app/setting.js?v=' . time()) . '"></script>
            ',
            'rooms' => [],
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
                    'is_connect' => 0,
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
            // session()->set(['userID' => 1]);
            $userID = hashidsDecrypt(session()->get('userID'));

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
        // session()->set(['userID' => 1]);
        return hashidsDecrypt(session()->get('userID'));
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

    public function index_message()
    {
        $userID = $this->initializeSession();

        $userSocials = $this->userSocialModel->getUserSocialByUserID($userID);

        return view('/app', [
            'content' => 'setting/message',
            'title' => 'Chat',
            'css_critical' => '
                <link href="' . base_url('assets/libs/sweetalert2/sweetalert2.min.css') . '" rel="stylesheet" type="text/css">
                <link href="' . base_url('assets/libs/animate.css/animate.min.css') . '" rel="stylesheet" type="text/css">
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
            ',
            'js_critical' => '
                <script src="https://code.jquery.com/jquery-3.7.1.js" crossorigin="anonymous"></script>
                <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
                <script src="' . base_url('assets/libs/sweetalert2/sweetalert2.min.js') . '"></script>       
                <script src="' . base_url('app/setting.js?v=' . time()) . '"></script>   
                <script src="' . base_url('app/message-setting.js?v=' . time()) . '"></script>
            ',
            'rooms' => [],
            'user_socials' => $userSocials,
        ]);
    }

    public function message_traning()
    {
        $buffer_datetime = date("Y-m-d H:i:s");
        $response = [
            'success' => 0,
            'message' => '',
        ];
        $status = 500;

        try {
            // session()->set(['userID' => 1]);
            $userID = hashidsDecrypt(session()->get('userID'));
            $data = $this->request->getJSON();

            $message_training = $data->message;
            $message_state = $data->message_status;

            $traning = $this->customerModel->insertMessageTraning([
                'user_id' => $userID,
                'message_training' => $message_training,
                'message_state' => $message_state
            ]);

            $GPTToken = getenv('GPT_TOKEN');
            $chatGPT = new ChatGPT([
                'GPTToken' => $GPTToken
            ]);

            //get message to promt
            $data_promt =  $this->customerModel->getMessageToPromt($userID);

            $data_promt_new = "";
            for ($i = 0; $i < count($data_promt); $i++) {
                $data_promt_new .= "\n" . (string)$data_promt[$i]->message_training;
            }

            // Builder
            $messageReplyBuilder = $chatGPT->gptBuilderChatGPT($data_promt_new);

            $messageReplyBuilder_back = $this->customerModel->insertMessageTraning([
                'user_id' => $userID,
                'message_training' => $messageReplyBuilder,
                'message_state' => 'A'
            ]);

            // Promt
            $messageReplyPrompt = $chatGPT->gennaratePromtChatGPT($data_promt_new);
            $message_data_user =  $this->userModel->getMessageTraningByID($userID);

            //get setting status
            if ($message_data_user) {
                $data_update = [
                    'message' => $messageReplyPrompt,
                    'updated_at' => $buffer_datetime
                ];
                $traning = $this->customerModel->updateMessageSetting($userID, $data_update);
            } else {
                $traning = $this->customerModel->insertMessageSetting([
                    'user_id' => $userID,
                    'message' => $messageReplyPrompt,
                    'message_status' => 'ON'
                ]);
            }


            $status = 200;
            $response['success'] = 1;
            $response['message'] = 'Traning สำเร็จ';
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setJSON($response);
    }

    public function message_traning_load($user_id)
    {
        $messageBack = $this->customerModel->getMessageTraningByID(hashidsDecrypt($user_id));

        $status = 200;
        $response = $messageBack;

        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setJSON($response);
    }

    public function message_traning_testing()
    {

        $response = [
            'success' => 0,
            'message' => '',
        ];
        $GPTToken = getenv('GPT_TOKEN');
        // CONNECT TO GPT
        $userID = hashidsDecrypt(session()->get('userID'));
        $message = $this->request->getPost('message');
        $file_askAI = $this->request->getFile('file_IMG');

        if ($file_askAI != NULL) {

            $file_askAI_name = $file_askAI->getRandomName();
            $file_askAI->move('uploads', $file_askAI_name);
            $file_Path = 'uploads/' . $file_askAI_name;

            $result_back = $this->s3Client->putObject([
                'Bucket' => $this->s3_bucket,
                'Key'    => 'uploads/img_ask_ai/' . $file_askAI_name,
                'Body'   => fopen($file_Path, 'r'),
                'ACL'    => 'public-read', // make file 'public'
            ]);

            if ($result_back['ObjectURL'] != "") {
                unlink('uploads/' . $file_askAI_name);
            }
        }

        $chatGPT = new ChatGPT([
            'GPTToken' => $GPTToken
        ]);

        $dataMessage = $this->customerModel->getMessageSettingByID($userID);
        $data_Message = $dataMessage ? $dataMessage->message : 'you are assistance';
        $img_link_back = "";
        if ($file_askAI == NULL) {
            $messageReplyToCustomer = $chatGPT->askChatGPT($message, $data_Message);
        } else {
            $messageReplyToCustomer = $chatGPT->askChatGPTimgTraning($message, $dataMessage->message, $this->s3_cdn_img . "/uploads/img_ask_ai/" . $file_askAI_name);
            $img_link_back = $this->s3_cdn_img . "/uploads/img_ask_ai/" . $file_askAI_name;
        }


        $status = 200;
        $response = [
            'success' => 1,
            'message' => $messageReplyToCustomer,
            'img_link' => $img_link_back
        ];

        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setJSON($response);
    }

    public function message_traning_clears()
    {

        $response = [
            'success' => 0,
            'message' => '',
        ];
        $status = 500;

        try {
            $data = $this->request->getJSON();
            $status_deletes_back = $this->customerModel->deletesMessageTraining(hashidsDecrypt($data->user_id));

            $status = 200;
            $response['success'] = 1;
            $response['message'] = 'Delete Traning สำเร็จ';
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }


        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setJSON($response);
    }
}
