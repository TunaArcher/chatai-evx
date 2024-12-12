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
            $UID = 'U0434fa7d7cfef4a035f9dce7c0253def';
            $userSocialID = '2';
            $accessToken = '';
            $channelID = '2006619676';
            $channelSecret = 'a5925643557a8ce364d47f2162257f30';
        } else {
            $userSocial = $this->userSocialModel->getUserSocialByID($messageRoom->user_social_id);

            $customer = $this->customerModel->getCustomerByID($messageRoom->customer_id);
            $UID = $customer->uid;

            $userSocialID =  $userSocial->id;
            $accessToken = $userSocial->line_channel_access_token;
            $channelID = $userSocial->line_channel_id;
            $channelSecret = $userSocial->line_channel_secret;
        }

        $lineAPI = new LineClient([
            'userSocialID' => $userSocialID,
            'accessToken' => $accessToken,
            'channelID' => $channelID,
            'channelSecret' => $channelSecret,
        ]);
        $send = $lineAPI->pushMessage($UID, $messageReplyToCustomer);
        log_message('info', 'ข้อความตอบไปที่ลูกค้า Line: ' . json_encode($messageReplyToCustomer, JSON_PRETTY_PRINT));

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

    public function handleReplyByAI($input, $userSocial)
    {
        $userID = session()->get('userID');

        // ดึงข้อมูลเหตุการณ์จาก Line API
        $event = $input->events[0];
        $UID = $event->source->userId;
        $message = $event->message->text;

        $GPTToken = getenv('GPT_TOKEN');

        // CONNECT TO GPT
        $chatGPT = new ChatGPT([
            'GPTToken' => $GPTToken
        ]);
        $messageReplyToCustomer = $chatGPT->askChatGPT($message);

        $customer = $this->customerModel->getCustomerByUIDAndPlatform($UID, $this->platform);
        $messageRoom = $this->messageRoomModel->getMessageRoomByCustomerID($customer->id);

        // ข้อมูล Mock สำหรับ Development
        if (getenv('CI_ENVIRONMENT') == 'development') {
            $UID = 'U0434fa7d7cfef4a035f9dce7c0253def';
            $userSocialID = '2';
            $accessToken = '';
            $channelID = '2006619676';
            $channelSecret = 'a5925643557a8ce364d47f2162257f30';
        } else {
            $userSocial = $this->userSocialModel->getUserSocialByID($messageRoom->user_social_id);

            $customer = $this->customerModel->getCustomerByID($messageRoom->customer_id);
            $UID = $customer->uid;

            $userSocialID =  $userSocial->id;
            $accessToken = $userSocial->line_channel_access_token;
            $channelID = $userSocial->line_channel_id;
            $channelSecret = $userSocial->line_channel_secret;
        }

        $lineAPI = new LineClient([
            'userSocialID' => $userSocialID,
            'accessToken' => $accessToken,
            'channelID' => $channelID,
            'channelSecret' => $channelSecret,
        ]);
        $send = $lineAPI->pushMessage($UID, $messageReplyToCustomer);
        log_message('info', 'ข้อความตอบไปที่ลูกค้า Line: ' . json_encode($messageReplyToCustomer, JSON_PRETTY_PRINT));

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
