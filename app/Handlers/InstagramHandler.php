<?php

namespace App\Handlers;

use App\Integrations\InstagramClient\InstagramClient;
use App\Models\UserSocialModel;
use App\Models\MessageRoomModel;
use App\Integrations\WhatsApp\WhatsAppClient;
use App\Services\MessageService;

class InstagramHandler
{
    private $platform = 'Instagram';

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
        if (getenv('CI_ENVIRONMENT') == 'development') $input = $this->getMockInstagramWebhookData();

        // ดึงข้อมูลเหตุการณ์จาก Whats App
        $entry = $input->entry[0] ?? null;
        $messaging = $entry->messaging[0] ?? null;
        $UID = $messaging->sender->id ?? null;
        $message = $messaging->message->text ?? null;

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
            $UID = '1090651699462050';
            $instagramToken = 'IGQWRQTkpFUThOVUlLZAkgxMXJVbFkxc1FCbjFRaXRoMWMzbk9yS1RVQ1RWaWZAJR1ZAscXRUdzdadm9pVjJZAa3hoRm5vaExweFBRUThUdmdyQkt6QlJlTFNtd2tIQ05Ed3d2Wm13bnRNUEwybVBtc2tGYjczM29qSW8ZD';
        } else {
            $userSocial = $this->userSocialModel->getUserSocialByID($messageRoom->user_social_id);
            $instagramToken = $userSocial->whatsapp_token;
        }

        $instagrampAPI = new InstagramClient([
            'accessToken' => $instagramToken
        ]);
        $send = $instagrampAPI->pushMessage($UID, $messageReplyToCustomer);
        log_message('info', 'ข้อความตอบไปที่ลูกค้า Instagram: ' . json_encode($messageReplyToCustomer, JSON_PRETTY_PRINT));

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
                                    "text": "Nj"
                                }
                            }
                        ]
                    }
                ]
            }'
        );
    }
}
