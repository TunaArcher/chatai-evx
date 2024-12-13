<?php

namespace App\Handlers;

use App\Integrations\Line\LineClient;
use App\Libraries\ChatGPT;
use App\Models\CustomerModel;
use App\Models\MessageRoomModel;
use App\Models\UserSocialModel;
use App\Services\MessageService;

class LineHandler
{
    private $platform = 'Line';

    private MessageService $messageService;
    private CustomerModel $customerModel;
    private MessageRoomModel $messageRoomModel;
    private UserSocialModel $userSocialModel;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
        $this->customerModel = new CustomerModel();
        $this->messageRoomModel = new MessageRoomModel();
        $this->userSocialModel = new UserSocialModel();
    }

    public function handleWebhook($input, $userSocial)
    {
        $input = $this->prepareWebhookInput($input, $userSocial);

        // ดึงข้อมูล Platform ที่ Webhook เข้ามา
        $event = $input->events[0];
        $UID = $event->source->userId;
        $message = $event->message->text;

        // ตรวจสอบหรือสร้างลูกค้า
        $customer = $this->messageService->getOrCreateCustomer($UID, $this->platform, $userSocial);

        // ตรวจสอบหรือสร้างห้องสนทนา
        $messageRoom = $this->messageService->getOrCreateMessageRoom($this->platform, $customer, $userSocial);

        // บันทึกข้อความและส่งต่อ WebSocket
        $this->processIncomingMessage($messageRoom, $customer, $message, 'Customer');
    }

    public function handleReplyByManual($input)
    {
        $userID = session()->get('userID');

        // ข้อความตอบกลับ
        $messageReply = $input->message;

        $messageRoom = $this->messageRoomModel->getMessageRoomByID($input->room_id);
        $UID = $this->getCustomerUID($messageRoom);

        $platformClient = $this->preparePlatformClient($messageRoom);
        $this->sendMessageToPlatform($platformClient, $UID, $messageReply, $messageRoom, $userID, 'Admin');
    }

    public function handleReplyByAI($input, $userSocial)
    {
        $input = $this->prepareWebhookInput($input, $userSocial);

        // ดึงข้อมูล Platform ที่ Webhook เข้ามา
        $event = $input->events[0];
        $UID = $event->source->userId;
        $message = $event->message->text;

        $chatGPT = new ChatGPT(['GPTToken' => getenv('GPT_TOKEN')]);
        // ข้อความตอบกลับ
        $messageReply = $chatGPT->askChatGPT($message);

        $customer = $this->customerModel->getCustomerByUIDAndPlatform($UID, $this->platform);
        $messageRoom = $this->messageRoomModel->getMessageRoomByCustomerID($customer->id);

        $platformClient = $this->preparePlatformClient($messageRoom);
        $this->sendMessageToPlatform($platformClient, $UID, $messageReply, $messageRoom, session()->get('userID'), 'Admin');
    }

    // -----------------------------------------------------------------------------
    // Helper
    // -----------------------------------------------------------------------------

    private function processIncomingMessage($messageRoom, $customer, $message, $sender)
    {
        $this->messageService->saveMessage(
            $messageRoom->id,
            $customer->id,
            $message,
            $this->platform,
            $sender
        );

        $this->messageService->sendToWebSocket([
            'room_id' => $messageRoom->id,
            'send_by' => $sender,
            'sender_id' => $customer->id,
            'message' => $message,
            'platform' => $this->platform,
            'sender_name' => $customer->name,
            'created_at' => date('Y-m-d H:i:s'),
            'sender_avatar' => $customer->profile,
        ]);
    }

    private function sendMessageToPlatform($platformClient, $UID, $message, $messageRoom, $userID, $sender)
    {
        $send = $platformClient->pushMessage($UID, $message);
        log_message('info', "ข้อความตอบไปที่ลูกค้า Message Room ID $messageRoom->id $this->platform: " . $message);

        if ($send) {

            $this->messageService->saveMessage($messageRoom->id, $userID, $message, $this->platform, $sender);

            $this->messageService->sendToWebSocket([
                'room_id' => $messageRoom->id,
                'send_by' => $sender,
                'sender_id' => $userID,
                'message' => $message,
                'platform' => $this->platform,
                'created_at' => date('Y-m-d H:i:s'),
                'sender_avatar' => '',
            ]);
        }
    }

    private function prepareWebhookInput($input, $userSocial)
    {
        if (getenv('CI_ENVIRONMENT') === 'development') {
            $input = $this->getMockLineWebhookData();
            $userSocial->line_channel_access_token = 'z7HhG1tz7PyWrRTt5kg79J2OZ7WZEKyA4wuyHzK65GoO/MnFugfaow/ob0iKSlFjr4U9+UVPpWY5xsNSPZznX2Z7KlPqAq8v/SJ1XiW8kgWmWw3gBINye4HU7yX+jWUuZlb8riafqfp5K7eUXqI1yY9PbdgDzCFqoOLOYbqAITQ=';
        }

        return $input;
    }

    private function preparePlatformClient($messageRoom)
    {
        $userSocial = $this->userSocialModel->getUserSocialByID($messageRoom->user_social_id);

        return new LineClient([
            'userSocialID' => $userSocial->id,
            'accessToken' => $userSocial->line_channel_access_token,
            'channelID' => $userSocial->line_channel_id,
            'channelSecret' => $userSocial->line_channel_secret,
        ]);
    }

    private function getCustomerUID($messageRoom)
    {
        $customer = $this->customerModel->getCustomerByID($messageRoom->customer_id);
        return $customer->uid;
    }

    private function getMockLineWebhookData()
    {
        return json_decode(
            '{
                "destination": "Udaa84d69ccfb66dc5144c24fc0bd9fa8",
                "events": [
                    {
                        "type": "message",
                        "message": {
                            "type": "text",
                            "id": "538866510690255010",
                            "quoteToken": "ZvhrKXFTGXHIlM2_Tgu6yqlBiOzH1CsdVor_IUNcJ4RGlwzcRxcdmNSjR5UANOHIhH-bFEwBrMGoMetWbdGR7TaFvyFVwiE7UQ6X5FLPNFxWb6JozzZ0-TZAbABTp7SiIlKgnbt8dBBEPHXPAIOFWg",
                            "text": "สวัสดี ทดสอบ"
                        },
                        "webhookEventId": "01JEXV2DVAHPZVC91Y9XPJ47JV",
                        "deliveryContext": {
                            "isRedelivery": false
                        },
                        "timestamp": 1734020773556,
                        "source": {
                            "type": "user",
                            "userId": "Ua03aec3ae1aeb9aa4c704aa69e14a966"
                        },
                        "replyToken": "ac51a9fa11664ac68ae81855b14db6d3",
                        "mode": "active"
                    }
                ]
            }'
        );
    }
}
