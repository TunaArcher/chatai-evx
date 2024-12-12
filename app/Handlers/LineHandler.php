<?php

namespace App\Handlers;

use App\Models\UserSocialModel;
use App\Models\MessageRoomModel;
use App\Integrations\Line\LineClient;
use App\Models\CustomerModel;
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
        // ข้อมูล Mock สำหรับ Development
        if (getenv('CI_ENVIRONMENT') == 'development') {
            $input = $this->getMockLineWebhookData();
            $userSocial->line_channel_access_token = 'UUvglmk7qWbUBSAzM2ThjtAtV+8ipnI1KabsWobuQt8VqFgizLGi91+eVfpZ86i9YRU/oWrmHSBFtACvAwZ/Z6rynrfHU4tWEQi6Yi/HhHzBjCeD5pMdPODqLaEbfCO5bX7rlAbD5swrrhQPljjhTgdB04t89/1O/w1cDnyilFU=';
        }

        // ดึงข้อมูลเหตุการณ์จาก Line API
        $event = $input->events[0];
        $UID = $event->source->userId;
        $message = $event->message->text;

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

    public function handleReplyByAI($input)
    {
        // CONNECT TO GPT
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
                            "text": "ข้อความทดสอบจาก Mockup"
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
