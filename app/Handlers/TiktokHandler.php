<?php

namespace App\Handlers;

use App\Models\UserSocialModel;
use App\Models\MessageRoomModel;
use App\Integrations\WhatsApp\WhatsAppClient;
use App\Services\MessageService;

class TiktokHandler
{
    private $platform = 'Tiktok';

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
        if (getenv('CI_ENVIRONMENT') == 'development') $input = $this->getMockTiktokWebhookData();

        // ดึงข้อมูลเหตุการณ์จาก Whats App
        $entry = $input->entry[0] ?? null;
        $changes = $entry->changes[0] ?? null;
        $value = $changes->value ?? null;
        $whatAppMessage = $value->messages[0] ?? null;
        $UID = $whatAppMessage->from ?? null; // เบอร์ของคนที่ส่งมา
        $message = $whatAppMessage->text->body ?? null; // ข้อความที่ส่งมา
        $contact = $value->contacts[0] ?? null;
        $name = $contact->profile->name ?? null;
        $waID = $contact->wa_id[0] ?? null;

        // ตรวจสอบหรือสร้างลูกค้า
        $customer = $this->messageService->getOrCreateCustomer($UID, $this->platform, $userSocial, $name);

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
            $phoneNumberID = '513951735130592';
            $UID = '66611188669';
            $whatsAppToken = 'EAAPwTXFKRgoBO3m1wcmZBUa92023EjuTrvFe5rAHKSO9se0pPoMyeQgZCxyvu3dQGLj8wyM0lXN8iuyvtzUCYinTRnfTKRrfYZCQYQ8EEdwlrB0rT6PjIOAlZCLN0dxernIo4SyWRY0p4IjsWFGpr34Y4KSMTUqwWVVFFWoUsvbxMB7NwTcZBvxd67nsW42ZA3rtrvtVFZAHG6VWfkiKMZB3DAqbpkUZD';
        } else {
            $userSocial = $this->userSocialModel->getUserSocialByID($messageRoom->user_social_id);
            $phoneNumberID = $userSocial->whatsapp_phone_number_id;
            $whatsAppToken = $userSocial->whatsapp_token;
        }

        $whatsAppAPI = new WhatsAppClient([
            'phoneNumberID' => $phoneNumberID,
            'whatsAppToken' => $whatsAppToken
        ]);
        $send = $whatsAppAPI->pushMessage($UID, $messageReplyToCustomer);
        log_message('info', 'ข้อความตอบไปที่ลูกค้า WhatsApp: ' . json_encode($messageReplyToCustomer, JSON_PRETTY_PRINT));

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

    private function getMockTiktokWebhookData()
    {
        return json_decode(
            '{
                "object": "whatsapp_business_account",
                "entry": [
                    {
                        "id": "520204877839971",
                        "changes": [
                            {
                                "value": {
                                    "messaging_product": "whatsapp",
                                    "metadata": {
                                        "display_phone_number": "15551868121",
                                        "phone_number_id": "513951735130592"
                                    },
                                    "contacts": [
                                        {
                                            "profile": {
                                                "name": "0611188669"
                                            },
                                            "wa_id": "66611188669"
                                        }
                                    ],
                                    "messages": [
                                        {
                                            "from": "66611188669",
                                            "id": "wamid.HBgLNjY2MTExODg2NjkVAgASGCA2RTdFNDY1NDYwQzlERjI2NjYyNjhCNTc5NzUwRkI0MgA=",
                                            "timestamp": "1733391693",
                                            "text": {
                                                "body": "."
                                            },
                                            "type": "text"
                                        }
                                    ]
                                },
                                "field": "messages"
                            }
                        ]
                    }
                ]
            }'
        );
    }
}
