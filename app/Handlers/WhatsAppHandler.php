<?php

namespace App\Handlers;

use App\Integrations\WhatsApp\WhatsAppClient;
use App\Libraries\ChatGPT;
use App\Models\CustomerModel;
use App\Models\MessageRoomModel;
use App\Models\UserSocialModel;
use App\Services\MessageService;

class LineHandler
{
    private $platform = 'WhatsApp';

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
        log_message('info', "ข้อความตอบไปที่ลูกค้า Message Room ID $messageRoom->id $this->platform: " . json_encode($message, JSON_PRETTY_PRINT));

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
            $input = $this->getMockWhatsAppWebhookData();
            $userSocial->whatsapp_phone_number_id = '513951735130592';
            $userSocial->whatsapp_token = 'EAAPwTXFKRgoBO3m1wcmZBUa92023EjuTrvFe5rAHKSO9se0pPoMyeQgZCxyvu3dQGLj8wyM0lXN8iuyvtzUCYinTRnfTKRrfYZCQYQ8EEdwlrB0rT6PjIOAlZCLN0dxernIo4SyWRY0p4IjsWFGpr34Y4KSMTUqwWVVFFWoUsvbxMB7NwTcZBvxd67nsW42ZA3rtrvtVFZAHG6VWfkiKMZB3DAqbpkUZD';
        }

        return $input;
    }

    private function preparePlatformClient($messageRoom)
    {
        $userSocial = $this->userSocialModel->getUserSocialByID($messageRoom->user_social_id);

        return new WhatsAppClient([
            'phoneNumberID' => $userSocial->whatsapp_phone_number_id,
            'whatsAppToken' => $userSocial->whatsapp_token
        ]);
    }

    private function getCustomerUID($messageRoom)
    {
        $customer = $this->customerModel->getCustomerByID($messageRoom->customer_id);

        if (getenv('CI_ENVIRONMENT') == 'development') return '66611188669';

        return $customer->uid;
    }

    private function getMockWhatsAppWebhookData()
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
