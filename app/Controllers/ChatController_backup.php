<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\MessageModel;
use App\Models\MessageRoomModel;
use App\Models\CustomerModel;
use CodeIgniter\HTTP\Message;

class ChatController extends BaseController
{
    public function __construct()
    {
        /*
        | -------------------------------------------------------------------------
        | SET ENVIRONMENT
        | -------------------------------------------------------------------------
        */

        /*
        | -------------------------------------------------------------------------
        | SET UTILITIES
        | -------------------------------------------------------------------------
        */

        // Model
        $this->UserModel = new UserModel();
        $this->MessageModel = new MessageModel();
        $this->MessageRoomModel = new MessageRoomModel();
        $this->CustomerModel = new CustomerModel();
    }

    public function index()
    {
        // สมมุติว่ากำลังใช้งาน Username ID 1 อยู่ // ตาราง users
        session()->set([
            'userID' => 1
        ]);

        $userID = session()->get('userID');

        $rooms = $this->MessageRoomModel->getMessageRoomByUserID($userID);

        $data['content'] = 'chat/index';
        $data['title'] = 'Chat';

        $data['js_critical'] = '<script src="app/chat.js"></script>';


        foreach ($rooms as $room) {

            $room->ic_platform = '';

            // หารูป
            if ($room->platform == 'Facebook') $room->ic_platform = 'ic-Facebook.svg';
            else if ($room->platform == 'Line') $room->ic_platform = 'ic-Line.png';
            else if ($room->platform == 'WhatsApp') $room->ic_platform = 'ic-WhatsApp.png';

            // หาชื่อลูกค้าที่ส่งมาจากโซเซียล
            $room->customer_name = $this->CustomerModel->getCustomerByID($room->customer_id)->name;

            // หาข้อความล่าสุด
            $lastMessage = $this->MessageModel->getLastMessageByRoomID($room->id);
            if ($lastMessage != '') {
                $room->last_message = $lastMessage->message;
                $room->last_time = $lastMessage->created_at;
            } else {
                $room->last_message = '';
                $room->last_time = '';
            }
        }

        $data['rooms'] = $rooms;

        return view('/app', $data); // โหลด View พร้อมข้อมูล
    }

    // addEmployee data 
    // public function addEmployee()
    // {
    //     $this->EmployeeModel = new \App\Models\EmployeeModel();
    //     try {
    //         // SET CONFIG
    //         $status = 500;
    //         $response['success'] = 0;
    //         $response['message'] = '';

    //         // HANDLE REQUEST
    //         $password = $this->request->getVar('password');
    //         $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    //         $thumbnail = '';

    //         if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['name'] != '') {
    //             $thumbnail = $this->ddoo_upload_img($this->request->getFile('thumbnail'));
    //         } else {
    //             $thumbnail = $this->request->getVar('avatar_Thumbnail');
    //         }

    //         $create = $this->EmployeeModel->insertEmployee([
    //             'name' => $this->request->getVar('name'),
    //             'nickname' => $this->request->getVar('nickname'),
    //             'phone_number' => $this->request->getVar('phone_number'),
    //             'employee_email' => $this->request->getVar('employee_email'),
    //             'branch_id' => $this->request->getVar('branch_id'),
    //             'position_id' => $this->request->getVar('position_id'),
    //             'username' => $this->request->getVar('username'),
    //             'password' => $hashed_password,
    //             'thumbnail' => $thumbnail,
    //             'created_by' => session()->get('username'),  
    //             'companies_id' => session()->get('companies_id')
    //         ]);
    //         // return redirect()->to('/employee/list');
    //         if ($create) {

    //             logger_store([
    //                 'employee_id' => session()->get('employeeID'),
    //                 'username' => session()->get('username'),
    //                 'event' => 'เพิ่ม',
    //                 'detail' => '[เพิ่ม] พนักงาน',
    //                 'ip' => $this->request->getIPAddress()
    //             ]);

    //             $status = 200;
    //             $response['success'] = 1;
    //             $response['message'] = 'เพิ่ม พนักงาน สำเร็จ';
    //         } else {
    //             $status = 200;
    //             $response['success'] = 0;
    //             $response['message'] = 'เพิ่ม พนักงาน ไม่สำเร็จ';
    //         }
    //         // print_r($response['success']);
    //         // exit();
    //         return $this->response
    //             ->setStatusCode($status)
    //             ->setContentType('application/json')
    //             ->setJSON($response);
    //     } catch (\Exception $e) {
    //         echo $e->getMessage() . ' ' . $e->getLine();
    //     }
    // }

    public function fetchMessages($roomID)
    {
        $messages = $this->MessageModel->getMessageRoomByRoomID($roomID);

        return $this->response->setJSON($messages); // ส่งข้อมูลข้อความกลับในรูปแบบ JSON
    }

    public function webhook()
    {
        // รับข้อมูลจาก Webhook
        $input = $this->request->getJSON();

        $messageModel = new MessageModel();
        $userModel = new UserModel();

        // ตรวจสอบว่าเป็นแพลตฟอร์มใด
        $platform = $input->platform;
        $senderId = $input->sender_id;
        $message = $input->message;

        // ค้นหาผู้ส่ง
        $user = $userModel->where('platform', $platform)->where('id', $senderId)->first();
        if (!$user) {
            return $this->response->setStatusCode(404, 'User not found');
        }

        // บันทึกข้อความลงในฐานข้อมูล
        // $messageModel->insert([
        //     'room_id' => $input->room_id,
        //     'sender_id' => $senderId,
        //     'receiver_id' => $input->receiver_id,
        //     'message' => $message,
        //     'platform' => $platform
        // ]);

        $messageModel->insert([
            'room_id' => $input->room_id,
            'send_by' => 1, // สมมติว่าเป็น Admin
            'sender_id' => 1,
            'message' => $input->message,
            'platform' => 'WebApp',
        ]);


        // ส่งข้อความไปยัง WebSocket Server
        $this->sendMessageToWebSocket($input);

        return $this->response->setJSON(['status' => 'success']);
    }

    public function sendMessage()
    {
        $input = $this->request->getJSON();

        $messageModel = new MessageModel();

        // บันทึกข้อความลงฐานข้อมูล
        // $messageModel->insert([
        //     'room_id' => $input->room_id,
        //     'sender_id' => 1, // สมมติว่าเป็น Admin
        //     'receiver_id' => 1,
        //     'message' => $input->message,
        //     'platform' => 'WebApp'
        // ]);

        $userID = session()->get('userID');

        $this->MessageModel->insertMessage([
            'room_id' => $input->room_id,
            'send_by' => 'Admin',
            'sender_id' => $userID,
            'message' => $input->message,
            'platform' => $input->platform,
        ]);

        // ส่งข้อความไปยัง WebSocket Server
        $this->sendMessageToWebSocket([
            'room_id' => $input->room_id,
            'send_by' => 'Admin',
            'sender_id' => $userID,
            'message' => $input->message,
            'platform' => $input->platform,
        ]);

        return $this->response->setJSON(['status' => 'success']);
    }


    private function sendMessageToWebSocket($data)
    {
        $url = 'http://localhost:8080'; // WebSocket Server
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        // fix bug
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // ไม่ส่ง Response กลับไปยัง Front-End 
        curl_exec($ch);
        curl_close($ch);
    }
}
