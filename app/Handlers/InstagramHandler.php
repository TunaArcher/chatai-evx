<?php

namespace App\Handlers;

use App\Integrations\Instagram\InstagramClient;
use App\Libraries\ChatGPT;
use App\Models\CustomerModel;
use App\Models\MessageRoomModel;
use App\Models\UserSocialModel;
use App\Services\MessageService;

class InstagramHandler
{
    private $platform = 'Instagram';

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
        $messaging = $entry->messaging[0] ?? null;
        $UID = $messaging->sender->id ?? null;
        $message = $messaging->message->text ?? null;

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
            $input = $this->getMockInstagramWebhookData();
            $userSocial->ig_token = 'IGQWRQTkpFUThOVUlLZAkgxMXJVbFkxc1FCbjFRaXRoMWMzbk9yS1RVQ1RWaWZAJR1ZAscXRUdzdadm9pVjJZAa3hoRm5vaExweFBRUThUdmdyQkt6QlJlTFNtd2tIQ05Ed3d2Wm13bnRNUEwybVBtc2tGYjczM29qSW8ZD';
        }

        return $input;
    }

    private function preparePlatformClient($messageRoom)
    {
        $userSocial = $this->userSocialModel->getUserSocialByID($messageRoom->user_social_id);

        return new InstagramClient([
            'accessToken' => $userSocial->ig_token
        ]);
    }

    private function getCustomerUID($messageRoom)
    {
        $customer = $this->customerModel->getCustomerByID($messageRoom->customer_id);

        if (getenv('CI_ENVIRONMENT') == 'development') return '1090651699462050';

        return $customer->uid;
    }

    private function getMockInstagramWebhookData()
    {
        return json_decode(
            '{
                "object": "instagram",
                "entry": [
                    {
                        "time": 1734002587325,
                        "id": "17841471550633446",
                        "messaging": [
                            {
                                "sender": {
                                    "id": "1090651699462050"
                                },
                                "recipient": {
                                    "id": "17841471550633446"
                                },
                                "timestamp": 1734002586774,
                                "message": {
                                    "mid": "aWdfZAG1faXRlbToxOklHTWVzc2FnZAUlEOjE3ODQxNDcxNTUwNjMzNDQ2OjM0MDI4MjM2Njg0MTcxMDMwMTI0NDI3NjAxNzM1NDQ3NjQ3MTk5ODozMTk4NjcwMTk0MTM3NTg1MTA1MTMxNzc4NDc5MjI2ODgwMAZDZD",
                                    "text": "ข้อความทดสอบ"
                                }
                            }
                        ]
                    }
                ]
            }'
        );
    }
}
