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
use Google\Service\CloudDeploy\DefaultPool;

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
    private $GPTToken;
    private $QWENToken;

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
        $this->GPTToken = getenv('GPT_TOKEN');
        $this->QWENToken = getenv('QWEN_TOKEN');

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

        function newArrayFilesName($file)
        {
            $file_ary = array();
            $file_count = count($file['name']);
            $file_key = array_keys($file);

            for ($i = 0; $i < $file_count; $i++) {
                foreach ($file_key as $val) {
                    $file_ary[$i][$val] = $file[$val][$i];
                }
            }
            return $file_ary;
        }

        function generateRandomString($length = 7)
        {
            $characters = '0123456789';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }

        function CSV_to_JSON($file_path, $jsonFile)
        {

            try {
                // เปิดไฟล์ CSV
                if (($handle = fopen($file_path, 'r')) !== FALSE) {
                    $data = [];
                    $headers = fgetcsv($handle); // อ่านบรรทัดแรกเป็น header

                    // อ่านข้อมูลที่เหลือ
                    while (($row = fgetcsv($handle)) !== FALSE) {
                        $data[] = array_combine($headers, $row);
                    }
                    fclose($handle);

                    // แปลงเป็น JSON และบันทึกลงไฟล์
                    file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                    return "success";
                }
            } catch (Exception $e) {
                return 'Error: ' . $e->getMessage();
            }
        }
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
                <link href="' . base_url('assets/libs/uppy/uppy.min.css') . '" rel="stylesheet" type="text/css">             
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
            ',
            'js_critical' => '
                <script src="https://code.jquery.com/jquery-3.7.1.js" crossorigin="anonymous"></script>
                <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>   
                <script src="' . base_url('assets/libs/uppy/uppy.legacy.min.js') . '"></script>                 
                <script src="' . base_url('assets/libs/sweetalert2/sweetalert2.min.js') . '"></script>       
                <script src="' . base_url('app/setting.js?v=' . time()) . '"></script>   
                <script src="' . base_url('app/message-setting.js?v=' . time()) . '"></script>
            ',
            'rooms' => [],
            'user_socials' => $userSocials,

            // <script src="' . base_url('assets/js/pages/file-upload.init.js') . '"></script>
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

            $chatGPT = new ChatGPT([
                'GPTToken' => $this->GPTToken
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

    public function message_setting_load($user_id)
    {
        $messageBack = $this->customerModel->getMessageSettingByID(hashidsDecrypt($user_id));

        $status = 200;
        $response = $messageBack;

        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setJSON($response);
    }

    public function message_traning_testing()
    {

        $buffer_datetime = date("Y-m-d H:i:s");
        $response = [
            'success' => 0,
            'message' => '',
        ];      
        // CONNECT TO GPT
        $userID = hashidsDecrypt(session()->get('userID'));
        $message = $this->request->getPost('message');
        $file_askAI = $this->request->getFile('file_IMG');

        $link_s3_file = "";
        if ($file_askAI != NULL) {

            $file_askAI_name = $file_askAI->getRandomName();
            $file_askAI->move('uploads', $file_askAI_name);
            $file_Path = 'uploads/' . $file_askAI_name;

            $result_back = $this->s3Client->putObject([
                'Bucket' => $this->s3_bucket,
                'Key'    => 'uploads/img/training/' . $file_askAI_name,
                'Body'   => fopen($file_Path, 'r'),
                'ACL'    => 'public-read',
            ]);

            if ($result_back['ObjectURL'] != "") {
                unlink('uploads/' . $file_askAI_name);
                $link_s3_file = $this->s3_cdn_img . "/uploads/img/training/" . $file_askAI_name;
            }
        }

        $chatGPT = new ChatGPT([
            'GPTToken' => $this->GPTToken,
            'QWENToken' => $this->QWENToken
        ]);

        $dataMessage = $this->customerModel->getMessageSettingByID($userID);
        $data_Message = $dataMessage ? $dataMessage->message : 'you are assistance';

        $img_link_back = null;
        //check file assistant
        if ($dataMessage->file_training_setting == '1') {
            $img_link_back = $link_s3_file == "" ? null : $link_s3_file;
            $img_link_back_ = "";
            if ($img_link_back != null) {
                $img_link_back_ = $img_link_back . ",";
            }
            $data_file_search = $this->customerModel->getTrainingAssistantByUserID($userID);
            $thread_id = $chatGPT->createthreadsTraining($img_link_back_, $message, $data_file_search->thread_id);
            if ($data_file_search->assistant_id != null) {
                //thread_id
                $thread_id_insert = $this->customerModel->updateTrainingAssistant($userID, [
                    'thread_id' => $thread_id['thread_id'],
                    'updated_at' => $buffer_datetime
                ]);
            }
            $messageReplyToCustomer = $chatGPT->sendmessagetoThreadId($thread_id['thread_id'], $data_file_search->assistant_id);
        } else {
            if ($file_askAI == NULL) {
                $messageReplyToCustomer = $chatGPT->askChatGPTTraininng($message, $data_Message);  
                // $messageReplyToCustomer = $chatGPT->askQwenTraininng($message, $data_Message);
            } else {
                $messageReplyToCustomer = $chatGPT->askChatGPTimgTraining($message, $dataMessage->message, $link_s3_file);
                // $messageReplyToCustomer = $chatGPT->askChatQwenimgTraining($message, $dataMessage->message, $link_s3_file);
                $img_link_back = $link_s3_file;
            }
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

    public function file_training()
    {
        //create file temp
        $filePaths = [];
        $buffer_datetime = date("Y-m-d H:i:s");
        $response = [
            'success' => 0,
            'message' => '',
        ];

        $switch_load_file = $_POST['switch_state'];
        $userID = hashidsDecrypt(session()->get('userID'));
        $multifile_training = newArrayFilesName($_FILES['files']);


        $chatGPT = new ChatGPT([
            'GPTToken' => $this->GPTToken
        ]);

        //update status messate to file data
        if ($switch_load_file == true) {
            $message_setting_status_update = $this->customerModel->updateMessageSetting($userID, [
                'file_training_setting' => 1,
                'updated_at' => $buffer_datetime
            ]);
        }

        // get message setting to setting assistants
        $message_setting  =  $this->customerModel->getMessageSettingByID($userID);
        // create ssistants
        $assistant_id  = $chatGPT->createAssistantsFileSearch($userID, $message_setting->message);

        $check_user_id = $this->customerModel->getTrainingAssistantByUserID($userID);
        // add or update assistants in Database table file_training.
        //get setting status
        if ($check_user_id) {
            //remove assistants for update 
            $removeassistent = $chatGPT->removeAssistant($check_user_id->assistant_id);
            $data_update = [
                'assistant_id' => $assistant_id,
                'thread_id' => null,
                'updated_at' => $buffer_datetime
            ];
            $assistant_id_status_insert = $this->customerModel->updateTrainingAssistant($userID, $data_update);
        } else {
            $assistant_id_status_insert = $this->customerModel->insertFileTrainingAssistant([
                'user_id' => $userID,
                'assistant_id' => $assistant_id,
            ]);
        }


        foreach ($multifile_training as $val) {
            $file_training_name = $userID . '_' . generateRandomString() . "." . pathinfo($val['name'], PATHINFO_EXTENSION);

            //check csv file convet to json 
            if ($val['type'] == 'text/csv') {
                $file_json =  $userID . '_' . generateRandomString() . "." . "json";
                move_uploaded_file($val['tmp_name'], './uploads/' . $file_training_name);
                //convert function
                $status_convert =  CSV_to_JSON('./uploads/' . $file_training_name, './uploads/' . $file_json);
                if ($status_convert == 'success') {

                    unlink('./uploads/' . $file_training_name);
                    $file_training_name =  $file_json;
                    move_uploaded_file($val['tmp_name'], './uploads/' . $file_training_name);
                }
            } else {
                move_uploaded_file($val['tmp_name'], './uploads/' . $file_training_name);
            }

            array_push($filePaths, 'uploads/' . $file_training_name);
        }

        // create vactor store file
        $reponse_vactor = $chatGPT->createVactorStore($userID);
        if ($reponse_vactor) {
            $vactor_id_insert = $this->customerModel->updateTrainingAssistant($userID, [
                'vactor_store_id' => $reponse_vactor['vactorstore_id'],
                'updated_at' => $buffer_datetime
            ]);
        }
        // create file upload to vactor store
        $response_file_upload =  $chatGPT->fileUpload($reponse_vactor['vactorstore_id'], $filePaths);
        // delete file temp
        if ($response_file_upload['file_id'] != "") {

            foreach ($filePaths as $filePath) {
                unlink($filePath);
            }

            //file id
            $file_id_insert = $this->customerModel->updateTrainingAssistant($userID, [
                'file_training_id' => $response_file_upload['file_id'],
                'updated_at' => $buffer_datetime
            ]);
        }
        //update Assistant add vactor
        $reponse_update = $chatGPT->addVatorfileToAssistant($assistant_id, $reponse_vactor['vactorstore_id']);

        $status = 200;
        $response = [
            'success' => 1,
            'message' => $reponse_update,
        ];

        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setJSON($response);
    }

    public function file_training_state()
    {
        $buffer_datetime = date("Y-m-d H:i:s");
        $userID = hashidsDecrypt(session()->get('userID'));
        $status = 500;
        $response = [
            'success' => 0,
            'message' => '',
        ];

        try {
            $switch_state = $this->request->getPost('switch_state');
            $state_file = array();

            if ($switch_state == "true") {
                $state_file = array(
                    'file_training_setting' => 1,
                    'updated_at' => $buffer_datetime
                );
            } else {
                $state_file = array(
                    'file_training_setting' => 0,
                    'updated_at' => $buffer_datetime
                );
            }

            $message_setting_status_update = $this->customerModel->updateMessageSetting($userID, $state_file);

            if ($message_setting_status_update) {
                $status = 200;
                $response = [
                    'success' => 1,
                    'message' => $message_setting_status_update,
                ];
            }
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setJSON($response);
    }

    public function message_setting_file($user_id)
    {
        $messageBack = $this->customerModel->getTrainingAssistantByUserID(hashidsDecrypt($user_id));

        $status = 200;
        $response = $messageBack;

        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setJSON($response);
    }
}
