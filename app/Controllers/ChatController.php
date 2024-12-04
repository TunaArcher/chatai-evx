<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\MessageModel;
use App\Models\MessageRoomModel;
use App\Models\UserSocialModel;
use App\Models\UserModel;

use App\Libraries\Line;

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

    // NOTE: ต้องจัดการ ID, Refactor foreach
    /**
     * ฟังก์ชันแสดงหน้าหลักของระบบ Chat
     * - โหลดข้อมูลห้องสนทนา
     * - เตรียมข้อมูลสำหรับ View
     */
    public function index()
    {
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
            $room->customer_name = $this->getCustomerName($room->customer_id);

            // ข้อความล่าสุด
            $lastMessage = $this->messageModel->getLastMessageByRoomID($room->id);
            $room->last_message = $lastMessage->message ?? '';
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
        $messages = $this->messageModel->getMessageRoomByRoomID($roomID);
        return $this->response->setJSON($messages);
    }

    /**
     * ฟังก์ชันสำหรับรับข้อมูล Webhook จากแพลตฟอร์มต่าง ๆ
     * - ตรวจสอบข้อมูล
     * - บันทึกข้อความในฐานข้อมูล
     * - ส่งข้อความไปยัง WebSocket Server
     */
    public function webhook($userSocialID)
    {
        $input = $this->request->getJSON();

        $userSocial = $this->userSocialModel->getUserSocialByID(hashidsDecrypt($userSocialID));

        $platform = $userSocial->platform;

        switch ($platform) {
            case 'Facebook':

                break;


            case 'Line':
                $this->handleWebHookLine($input, $userSocial);
                // log_message('error', 'Webhook Input: ' . json_encode($input));

                // echo '<pre>';
                // print_r($input);
                // exit();

                break;

            case 'WhatsApp':
                break;

            case 'Instagram':
                break;

            case 'Tiktok':
                break;
        }

        return $this->response->setJSON(['status' => 'success']);
    }

    public function handleWebHookLine($input, $userSocial)
    {
        $platform = 'Line';

        // ข้อมูล Mock สำหรับ Development
        if (getenv('CI_ENVIRONMENT') == 'development') $input = $this->getMockLineWebhookData();

        // ดึงข้อมูลเหตุการณ์จาก Line API
        $user = $input->events[0];
        $type = $user->source->type;
        $UID = $user->source->userId;
        $message = $user->message->text;

        // ตรวจสอบลูกค้าจาก UID และ Platform
        $customer = $this->customerModel->getCustomerByUIDAndPlatform($UID, $platform);

        // Case 1: ถ้ายังไม่มี ให้ไปสร้างห้อง แล้ว send message
        if (!$customer) {

            // ให้สร้างยูส
            $this->customerModel->getCustomerByUIDAndPlatform($UID, $platform);

            // TODO:: Handle get profile

            $customer = $this->customerModel->insertCustomer([
                'platform' => 'LINE',
                'uid' => $UID,
                'profile' => ''
            ]);
            $customerID = $customer;

            if ($customer) {

                $messageRoom = $this->messageRoomModel->insertMessageRoom([
                    'platform' => 'Line',
                    'user_social_id' => $userSocial->id,
                    'user_social_name' => $userSocial->name,
                    'customer_id' => $customerID,
                    'user_id' => $userSocial->user_id,
                ]);
                $messageRoomID = $messageRoom;
            }
        }

        // Case 2: ถ้ามีแล้วแปลงว่าเป็นยูสที่เคยส่งข้อความมาแล้ว ให้ค้นหาห้อง แล้ว send message
        else {

            $customerID = $customer->id;

            $messageRoom = $this->messageRoomModel->getMessageRoomByCustomerID($customerID);
            $messageRoomID = $messageRoom->id;
        }

        // บันทึกข้อความลงฐานข้อมูล
        $this->messageModel->insertMessage([
            'room_id' => $messageRoomID,
            'send_by' => 'Customer',
            'sender_id' => $customerID,
            'message' => $message,
            'platform' => $platform,
        ]);

        // ส่งข้อความไปยัง WebSocket Server
        sendMessageToWebSocket([
            'room_id' => $messageRoomID,
            'send_by' => 'Customer',
            'sender_id' => $customer,
            'message' => $message,
            'platform' => $platform,
        ]);
    }

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
                // log_message('error', 'Webhook Input: ' . json_encode($input));

                // echo '<pre>';
                // print_r($input);
                // exit();

                break;

            case 'WhatsApp':
                break;

            case 'Instagram':
                break;

            case 'Tiktok':
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
                $uid = 'U0434fa7d7cfef4a035f9dce7c0253def';
                $channelAccessToken = 'UUvglmk7qWbUBSAzM2ThjtAtV+8ipnI1KabsWobuQt8VqFgizLGi91+eVfpZ86i9YRU/oWrmHSBFtACvAwZ/Z6rynrfHU4tWEQi6Yi/HhHzBjCeD5pMdPODqLaEbfCO5bX7rlAbD5swrrhQPljjhTgdB04t89/1O/w1cDnyilFU=';
            } else {
                $messageRoom = $this->messageRoomModel->getMessageRoomByID($input->room_id);
                $userSocial = $this->userSocialModel->getUserSocialByID($messageRoom->user_social_id);
                $channelAccessToken = $userSocial->line_channel_access_token;
            }

            $lineAPI = new Line(['channelAccessToken' => $channelAccessToken]);
            $sendToLine = $lineAPI->pushMessage($uid, $input->message);

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
                    'platform' => $platform
                ]);
            }
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'LineAPI::handleSendMessageLine error {message}', ['message' => $e->getMessage()]);
        }
    }

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

    /**
     * ฟังก์ชันสำหรับดึงชื่อลูกค้าจาก customerID
     * - หากไม่พบลูกค้า คืนค่า 'Unknown'
     */
    private function getCustomerName(int $customerID): string
    {
        $customer = $this->customerModel->getCustomerByID($customerID);
        return $customer->name ?? 'Unknown';
    }

    private function getMockLineWebhookData()
    {
        return json_decode(
            '{
                "destination": "Uebfcefae558f36b52310a78674602ef1",
                "events": [
                    {
                        "type": "message",
                        "message": {
                            "type": "text",
                            "id": "537640104437743685",
                            "quoteToken": "YjpCuVg0NaKnHYNYbdyFIfUY4dBolOvHmRVc4mbvKxxwMD73WbGS4CmSTn139cz7bWfLO9wMDyclaa34qFBw3nJwy7RVIxP2ogHAc2elrPm8RGtzLXtCriv_KV2c5f8XtXYqz1NirIOhDphNTjdzag",
                            "text": "Mmm"
                        },
                        "webhookEventId": "01JE81Y7QRESCF081NGE7TTESX",
                        "deliveryContext": { "isRedelivery": false },
                        "timestamp": 1733289778596,
                        "source": {
                            "type": "user",
                            "userId": "U0434fa7d7cfef4a035f9dce7c0253def"
                        },
                        "replyToken": "54fc984b6d054cc391d47b0f7ef2b902",
                        "mode": "active"
                    }
                ]
            }'
        );
    }
}
