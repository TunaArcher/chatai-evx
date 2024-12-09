<?php

namespace App\Handlers;

use App\Models\UserSocialModel;
use App\Models\MessageRoomModel;
use App\Integrations\Facebook\FacebookClient;
use App\Services\MessageService;

class FacebookHandler
{
    private $platform = 'WhatsApp';

    private MessageService $messageService;
    private MessageRoomModel $messageRoomModel;
    private UserSocialModel $userSocialModel;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
        $this->messageRoomModel = new MessageRoomModel();
        $this->userSocialModel = new UserSocialModel();
    }

    public function handleWebhook($input, $userSocial): void
    {
        // ข้อมูล Mock สำหรับ Development
        if (getenv('CI_ENVIRONMENT') == 'development') $input = $this->getMockFacebookWebhookData();

        // ดึงข้อมูลเหตุการณ์จาก facebook
        $message = $input->value->message->text ?? null;
        $UID = $input->value->sender->id ?? null;

        // ตรวจสอบหรือสร้างลูกค้า
        $customer = $this->messageService->getOrCreateCustomer($UID, $this->platform, $userSocial);

        // ตรวจสอบหรือสร้างห้องสนทนา
        $messageRoom = $this->messageService->getOrCreateMessageRoom($this->platform, $customer, $userSocial);

        // บันทึกข้อความในฐานข้อมูล
        $this->messageService->saveMessage($messageRoom->id, $customer->id, $message, $this->platform, 'Customer');

        // ส่งข้อความไปยัง WebSocket Server
        $this->messageService->sendToWebSocket([
            'room_id' => $messageRoom->id,
            'send_by' => 'Customer',
            'sender_id' => $customer->id,
            'message' => $message,
            'platform' => $this->platform,
            'sender_name' => $customer->name,
            'created_at' => date('Y-m-d H:i:s'),
            'sender_avatar' => $customer->profile,
        ]);
    }

    public function handleReplyByManual($input)
    {
        $userID = session()->get('userID');
        $messageReplyToCustomer = $input->message;
        $messageRoom = $this->messageRoomModel->getMessageRoomByID($input->room_id);

        // ข้อมูล Mock สำหรับ Development
        if (getenv('CI_ENVIRONMENT') == 'development') {
            $UID = '9158866310814762';
            $facebookToken = 'EAAPwTXFKRgoBO3m1wcmZBUa92023EjuTrvFe5rAHKSO9se0pPoMyeQgZCxyvu3dQGLj8wyM0lXN8iuyvtzUCYinTRnfTKRrfYZCQYQ8EEdwlrB0rT6PjIOAlZCLN0dxernIo4SyWRY0p4IjsWFGpr34Y4KSMTUqwWVVFFWoUsvbxMB7NwTcZBvxd67nsW42ZA3rtrvtVFZAHG6VWfkiKMZB3DAqbpkUZD';
        } else {
            $userSocial = $this->userSocialModel->getUserSocialByID($messageRoom->user_social_id);
            $facebookToken = $userSocial->fb_token;
        }

        $facebookAPI = new FacebookClient([
            'facebookToken' => $facebookToken
        ]);
        $send = $facebookAPI->pushMessage($UID, $messageReplyToCustomer);
        log_message('info', 'ข้อความตอบไปที่ลูกค้า Facebook: ' . json_encode($messageReplyToCustomer, JSON_PRETTY_PRINT));

        if ($send) {

            // บันทึกข้อความในฐานข้อมูล
            $this->messageService->saveMessage($messageRoom->id, $userID, $messageReplyToCustomer, $this->platform, 'Admin');

            // ส่งข้อความไปยัง WebSocket Server
            $this->messageService->sendToWebSocket([
                'room_id' => $messageRoom->id,
                'send_by' => 'Admin',
                'sender_id' => $userID,
                'message' => $messageReplyToCustomer,
                'platform' => $this->platform,
                // 'sender_name' => $customer->name,
                'created_at' => date('Y-m-d H:i:s'),
                'sender_avatar' => '',
            ]);
        }
    }

    public function handleReplyByAI($input)
    {
        // CONNECT TO GPT
    }

    private function getMockFacebookWebhookData()
    {
        return json_decode(
            '{
  "field": "messages",
  "value": {
    "sender": {
      "id": "9158866310814762"
    },
    "recipient": {
      "id": "23245"
    },
    "timestamp": "1527459824",
    "message": {
      "mid": "test_message_id",
      "text": "test_message",
      "commands": [
        {
          "name": "command123"
        },
        {
          "name": "command456"
        }
      ]
    }
  }
}'
        );
    }
}
