<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\MessageModel;
use App\Models\MessageRoomModel;
use App\Models\CustomerModel;

class ChatController extends BaseController
{
    private UserModel $userModel;
    private MessageModel $messageModel;
    private MessageRoomModel $messageRoomModel;
    private CustomerModel $customerModel;

    /**
     * Constructor สำหรับเตรียม Model ที่จำเป็น
     */
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->messageModel = new MessageModel();
        $this->messageRoomModel = new MessageRoomModel();
        $this->customerModel = new CustomerModel();
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
    public function webhook()
    {
        $input = $this->request->getJSON();

        // ตรวจสอบว่าผู้ส่งข้อความมีอยู่ในระบบหรือไม่
        $user = $this->userModel
            ->where('platform', $input->platform)
            ->where('id', $input->sender_id)
            ->first();

        if (!$user) {
            return $this->response->setStatusCode(404, 'User not found');
        }

        // บันทึกข้อความลงฐานข้อมูล
        $this->messageModel->insert([
            'room_id' => $input->room_id,
            'send_by' => 'Admin',
            'sender_id' => $input->sender_id,
            'message' => $input->message,
            'platform' => $input->platform,
        ]);

        // ส่งข้อความไปยัง WebSocket Server
        $this->sendMessageToWebSocket($input);

        return $this->response->setJSON(['status' => 'success']);
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
        $userID = session()->get('userID');

        // เตรียมข้อมูลสำหรับบันทึกลงฐานข้อมูล
        $messageData = [
            'room_id' => $input->room_id,
            'send_by' => 'Admin',
            'sender_id' => $userID,
            'message' => $input->message,
            'platform' => $input->platform
        ];

        // บันทึกข้อความลงในฐานข้อมูล
        $this->messageModel->insertMessage($messageData);

        // เตรียมข้อมูลสำหรับส่งไปยัง WebSocket Server
        $websocketData = [
            'room_id' => $input->room_id,
            'send_by' => 'Admin',
            'sender_id' => $userID,
            'message' => $input->message,
            'platform' => $input->platform
        ];

        // ส่งข้อความไปยัง WebSocket Server
        $this->sendMessageToWebSocket($websocketData);

        return $this->response->setJSON(['status' => 'success']);
    }

    /**
     * ฟังก์ชันสำหรับส่งข้อความไปยัง WebSocket Server
     * - ใช้ cURL เพื่อส่งข้อมูล
     */
    private function sendMessageToWebSocket(array $data)
    {
        $url = 'http://localhost:8080'; // URL ของ WebSocket Server
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // แปลงข้อมูลเป็น JSON
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // ไม่ต้องการรับ Response กลับ
        curl_exec($ch);
        curl_close($ch);
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
}
