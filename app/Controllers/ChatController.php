<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\MessageModel;
use App\Models\MessageRoomModel;
use App\Models\UserSocialModel;
use App\Models\UserModel;

use App\Libraries\Line;
use App\Libraries\WhatsApp;

use CodeIgniter\HTTP\ResponseInterface;

class ChatController extends BaseController
{
    private CustomerModel $customerModel;
    private MessageModel $messageModel;
    private MessageRoomModel $messageRoomModel;
    private UserModel $userModel;
    private UserSocialModel $userSocialModel;


    /**
     * Constructor สำหรับเตรียม Model ที่จำเป็น
     */
    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->messageModel = new MessageModel();
        $this->messageRoomModel = new MessageRoomModel();
        $this->userModel = new UserModel();
        $this->userSocialModel = new UserSocialModel();
    }

    /**
     * ฟังก์ชันแสดงหน้าหลักของระบบ Chat
     * - โหลดข้อมูลห้องสนทนา
     * - เตรียมข้อมูลสำหรับ View
     */
    public function index()
    {
        // TODO:: HANDLE
        // NOTE:: ต้องจัดการ ID, Refactor foreach
        
        // Mock userID สำหรับ Session (สมมติว่าผู้ใช้ ID 1 กำลังล็อกอิน)
        session()->set(['userID' => 1]);
        $userID = session()->get('userID');

        // ดึงรายการห้องสนทนา
        $rooms = $this->messageRoomModel->getMessageRoomByUserID($userID);

        // เตรียมข้อมูลเพิ่มเติมให้แต่ละห้อง
        foreach ($rooms as $room) {
            // ไอคอนแพลตฟอร์ม
            $room->ic_platform = $this->getPlatformIcon($room->platform);

            // ชื่อลูกค้า
            $customer = $this->customerModel->getCustomerByID($room->customer_id);
            $room->customer_name = $customer->name;
            $room->profile = $customer->profile;

            // ข้อความล่าสุด
            $lastMessage = $this->messageModel->getLastMessageByRoomID($room->id);

            $prefix = '';
            if ($lastMessage->send_by == 'Admin') $prefix = 'คุณ: ';
            $room->last_message = $lastMessage->message ?  $prefix . $lastMessage->message : '';
            $room->last_time = $lastMessage->created_at ?? '';
        }

        // ส่งข้อมูลไปยัง View
        return view('/app', [
            'content' => 'chat/index', // ชื่อไฟล์ View
            'title' => 'Chat', // ชื่อหน้า
            'js_critical' => '<script src="app/chat.js"></script>', // ไฟล์ JS
            'rooms' => $rooms // ข้อมูลห้องสนทนา
        ]);
    }

    /**
     * ฟังก์ชันสำหรับดึงข้อความในห้องสนทนาตาม roomID
     * - คืนค่าข้อมูลในรูปแบบ JSON
     */
    public function fetchMessages($roomID)
    {
        $room = $this->messageRoomModel->getMessageRoomByID($roomID);
        $customer = $this->customerModel->getCustomerByID($room->customer_id);
        $messages = $this->messageModel->getMessageRoomByRoomID($roomID);
        $data = [
            'room' => $room,
            'customer' => $customer,
            'messages' => $messages
        ];

        return $this->response->setJSON(json_encode($data));
    }

    // -----------------------------------------------------------------------------
    // ส่วนจัดการ การส่งข้อความ
    // -----------------------------------------------------------------------------

    /**
     * ฟังก์ชันสำหรับส่งข้อความจากฝั่ง Admin
     * - บันทึกข้อความในฐานข้อมูล
     * - ส่งข้อความไปยัง WebSocket Server
     * - คืนค่า JSON Response
     */
    public function sendMessage()
    {
        $input = $this->request->getJSON();

        $platform = $input->platform;

        switch ($platform) {
            case 'Facebook':

                break;


            case 'Line':
                $this->handleSendMessageLine($input);
                break;

            case 'WhatsApp':
                $this->handleSendMessageWhatsApp($input);
                break;

            case 'Instagram':
                break;

            case 'Tiktok':
                // $this->handleSendMessageTiktok($input);
                break;
        }

        return $this->response->setJSON(['status' => 'success']);
    }

    public function handleSendMessageLine($input)
    {
        try {

            $platform = 'Line';

            $input = $this->request->getJSON();
            $userID = session()->get('userID');

            // ข้อมูล Mock สำหรับ Development
            if (getenv('CI_ENVIRONMENT') == 'development') {
                $UID = 'U0434fa7d7cfef4a035f9dce7c0253def';
                $channelAccessToken = 'UUvglmk7qWbUBSAzM2ThjtAtV+8ipnI1KabsWobuQt8VqFgizLGi91+eVfpZ86i9YRU/oWrmHSBFtACvAwZ/Z6rynrfHU4tWEQi6Yi/HhHzBjCeD5pMdPODqLaEbfCO5bX7rlAbD5swrrhQPljjhTgdB04t89/1O/w1cDnyilFU=';
            } else {
                $messageRoom = $this->messageRoomModel->getMessageRoomByID($input->room_id);
                $userSocial = $this->userSocialModel->getUserSocialByID($messageRoom->user_social_id);
                $channelAccessToken = $userSocial->line_channel_access_token;
            }

            $lineAPI = new Line(['channelAccessToken' => $channelAccessToken]);
            $sendToLine = $lineAPI->pushMessage($UID, $input->message);

            if ($sendToLine) {
                // บันทึกข้อความลงในฐานข้อมูล
                $this->messageModel->insertMessage([
                    'room_id' => $input->room_id,
                    'send_by' => 'Admin',
                    'sender_id' => $userID,
                    'message' => $input->message,
                    'platform' => $platform
                ]);

                // ส่งข้อความไปยัง WebSocket Server
                sendMessageToWebSocket([
                    'room_id' => $input->room_id,
                    'send_by' => 'Admin',
                    'sender_id' => $userID,
                    'message' => $input->message,
                    'platform' => $platform,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'ChatController::handleSendMessageLine error {message}', ['message' => $e->getMessage()]);
        }
    }

    public function handleSendMessageWhatsApp($input)
    {
        try {

            $platform = 'WhatsApp';

            $input = $this->request->getJSON();
            $userID = session()->get('userID');

            // ข้อมูล Mock สำหรับ Development
            if (getenv('CI_ENVIRONMENT') == 'development') {
                $phoneNumberID = '513951735130592';
                $UID = '66611188669';
                $whatsAppToken = 'EAAPwTXFKRgoBO3m1wcmZBUa92023EjuTrvFe5rAHKSO9se0pPoMyeQgZCxyvu3dQGLj8wyM0lXN8iuyvtzUCYinTRnfTKRrfYZCQYQ8EEdwlrB0rT6PjIOAlZCLN0dxernIo4SyWRY0p4IjsWFGpr34Y4KSMTUqwWVVFFWoUsvbxMB7NwTcZBvxd67nsW42ZA3rtrvtVFZAHG6VWfkiKMZB3DAqbpkUZD';
            } else {
                $messageRoom = $this->messageRoomModel->getMessageRoomByID($input->room_id);
                $userSocial = $this->userSocialModel->getUserSocialByID($messageRoom->user_social_id);
                $phoneNumberID = $userSocial->whatsapp_phone_number_id;
                $whatsAppToken = $userSocial->whatsapp_token;
            }

            $whatsAppAPI = new WhatsApp([
                'phoneNumberID' => $phoneNumberID,
                'whatsAppToken' => $whatsAppToken
            ]);
            $sendToWhatsApp = $whatsAppAPI->pushMessage($UID, $input->message);

            if ($sendToWhatsApp) {
                // บันทึกข้อความลงในฐานข้อมูล
                $this->messageModel->insertMessage([
                    'room_id' => $input->room_id,
                    'send_by' => 'Admin',
                    'sender_id' => $userID,
                    'message' => $input->message,
                    'platform' => $platform
                ]);

                // ส่งข้อความไปยัง WebSocket Server
                sendMessageToWebSocket([
                    'room_id' => $input->room_id,
                    'send_by' => 'Admin',
                    'sender_id' => $userID,
                    'message' => $input->message,
                    'platform' => $platform,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'ChatController::handleSendMessageWhatsApp error {message}', ['message' => $e->getMessage()]);
        }
    }

    // -----------------------------------------------------------------------------
    // Helper
    // -----------------------------------------------------------------------------

    /**
     * ฟังก์ชันสำหรับคืนค่าไอคอนของแพลตฟอร์ม
     * - รองรับ Facebook, Line, WhatsApp
     */
    private function getPlatformIcon(string $platform): string
    {
        return match ($platform) {
            'Facebook' => 'ic-Facebook.svg',
            'Line' => 'ic-Line.png',
            'WhatsApp' => 'ic-WhatsApp.png',
            default => '',
        };
    }
}
