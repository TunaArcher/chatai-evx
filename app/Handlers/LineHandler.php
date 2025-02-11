<?php

namespace App\Handlers;

use App\Integrations\Line\LineClient;
use App\Libraries\ChatGPT;
use App\Models\CustomerModel;
use App\Models\MessageModel;
use App\Models\MessageRoomModel;
use App\Models\UserModel;
use App\Models\UserSocialModel;
use App\Services\MessageService;

class LineHandler
{
    private $platform = 'Line';

    private MessageService $messageService;

    private CustomerModel $customerModel;
    private MessageModel $messageModel;
    private MessageRoomModel $messageRoomModel;
    private UserModel $userModel;
    private UserSocialModel $userSocialModel;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;

        $this->customerModel = new CustomerModel();
        $this->messageModel = new MessageModel();
        $this->messageRoomModel = new MessageRoomModel();
        $this->userModel = new UserModel();
        $this->userSocialModel = new UserSocialModel();
    }

    public function handleWebhook($input, $userSocial)
    {
        $input = $this->prepareWebhookInput($input, $userSocial);

        // ดึงข้อมูล Platform ที่ Webhook เข้ามา
        // ตรวจสอบว่าเป็น Message ข้อความ, รูปภาพ, เสียง และจัดการ
        $message = $this->processMessage($input, $userSocial);

        // ตรวจสอบหรือสร้างลูกค้า
        $customer = $this->messageService->getOrCreateCustomer($message['UID'], $this->platform, $userSocial);

        // ตรวจสอบหรือสร้างห้องสนทนา
        $messageRoom = $this->messageService->getOrCreateMessageRoom($this->platform, $customer, $userSocial);

        // บันทึกข้อความและส่งต่อ WebSocket
        $this->processIncomingMessage(
            $messageRoom,
            $customer,
            $message['type'],
            $message['content'],
            'Customer',
        );

        return $messageRoom;
    }

    public function handleReplyByManual($input)
    {
        $messageReply = $input['message'];
        $messageType = $input['message_type'];

        $userID = hashidsDecrypt(session()->get('userID'));
        $messageRoom = $this->messageRoomModel->getMessageRoomByID($input['room_id']);
        $UID = $this->getCustomerUID($messageRoom);

        $platformClient = $this->preparePlatformClient($messageRoom);

        $this->sendMessageToPlatform(
            $platformClient,
            $UID,
            $messageType,
            $messageReply,
            $messageRoom,
            $userID,
            'Admin',
            'MANUAL'
        );

        $this->messageModel->clearUserContext($messageRoom->id);
    }

    public function handleReplyByAI($messageRoom, $userSocial)
    {
        $userID =  $userSocial->user_id;
        $dataMessage = $this->userModel->getMessageTraningByID($userID);

        $customer = $this->customerModel->getCustomerByID($messageRoom->customer_id);
        $UID = $customer->uid;

        $messages = $this->messageModel->getMessageNotReplyBySendByAndRoomID('Customer', $messageRoom->id);
        $message = $this->getUserContext($messages);

        // ข้อความตอบกลับ
        $chatGPT = new ChatGPT(['GPTToken' => getenv('GPT_TOKEN')]);
        $dataMessage = $dataMessage ? $dataMessage->message : '';

        $messageReply = $message['img_url'] == ''
            ? $chatGPT->askChatGPT($messageRoom->id, $message['message'], $dataMessage)
            : $chatGPT->askChatGPT($messageRoom->id, $message['message'], $dataMessage, $message['img_url']);

        $customer = $this->customerModel->getCustomerByUIDAndPlatform($UID, $this->platform);
        $messageRoom = $this->messageRoomModel->getMessageRoomByCustomerID($customer->id);

        $platformClient = $this->preparePlatformClient($messageRoom);

        $this->sendMessageToPlatform(
            $platformClient,
            $UID,
            $messageType = 'text',
            $messageReply,
            $messageRoom,
            $userID,
            'Admin',
            'AI'
        );

        $this->messageModel->clearUserContext($messageRoom->id);
    }

    private function getUserContext($messages)
    {
        helper('function');

        $contextText = '';
        $imageUrl = '';

        foreach ($messages as $message) {
            switch ($message->message_type) {
                case 'text':
                    $contextText .= $message->message . ' ';
                    break;
                case 'image':
                    $imageUrl .= $message->message . ',';
                    break;
                case 'audio':
                    $contextText .= convertAudioToText($message->message, $this->platform) . ' ';
                    break;
            }
        }

        return  [
            'message' => $contextText,
            'img_url' => $imageUrl,
        ];
    }

    // -----------------------------------------------------------------------------
    // Helper
    // -----------------------------------------------------------------------------

    private function processMessage($input, $userSocial)
    {
        $event = $input->events[0];
        $UID = $event->source->userId;
        // $message = $event->message->text;

        $eventType = $event->message->type;

        switch ($eventType) {

                // เคสข้อความ
            case 'text':
                $messageType = 'text';
                $message = $event->message->text;
                break;

                // เคสรูปภาพหรือ attachment อื่น ๆ
            case 'image':

                $messageType = 'image';

                $messageId = $event->message->id;
                $lineAccessToken = $userSocial->line_channel_access_token;

                $url = "https://api-data.line.me/v2/bot/message/{$messageId}/content";
                $headers = ["Authorization: Bearer {$lineAccessToken}"];

                // ดึงข้อมูลไฟล์จาก Webhook LINE
                $fileContent = fetchFileFromWebhook($url, $headers);

                // ตั้งชื่อไฟล์แบบสุ่ม
                $fileName = uniqid('line_') . '.jpg';

                // อัปโหลดไปยัง Spaces
                $message = uploadToSpaces(
                    $fileContent,
                    $fileName,
                    $messageType,
                    $this->platform
                );

                break;

                // เคสเสียง
            case 'audio':
                $messageType = 'audio';

                $messageId = $event->message->id;
                $lineAccessToken = $userSocial->line_channel_access_token;

                $url = "https://api-data.line.me/v2/bot/message/{$messageId}/content";
                $headers = ["Authorization: Bearer {$lineAccessToken}"];

                // ดึงข้อมูลไฟล์จาก Webhook LINE
                $fileContent = fetchFileFromWebhook($url, $headers);

                // ตั้งชื่อไฟล์แบบสุ่ม
                $fileName = uniqid('line_') . '.m4a';

                // อัปโหลดไปยัง DigitalOcean Spaces
                $message = uploadToSpaces(
                    $fileContent,
                    $fileName,
                    $messageType,
                    $this->platform
                );

                break;

            default;
        }

        return [
            'UID' => $UID,
            'type' => $messageType,
            'content' => $message,
        ];
    }

    private function processIncomingMessage($messageRoom, $customer, $messageType, $message, $sender)
    {
        $this->messageService->saveMessage(
            $messageRoom->id,
            $customer->id,
            $messageType,
            $message,
            $this->platform,
            $sender,
        );

        $this->messageService->sendToWebSocket([
            'messageRoom' => $messageRoom,

            'room_id' => $messageRoom->id,

            'send_by' => $sender,

            'sender_id' => $customer->id,
            'sender_name' => $customer->name,
            'sender_avatar' => $customer->profile,

            'platform' => $this->platform,
            'message_type' => $messageType,
            'message' => $message,

            'receiver_id' => hashidsEncrypt($messageRoom->user_id),
            'receiver_name' => 'Admin',
            'receiver_avatar' => '',

            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function sendMessageToPlatform($platformClient, $UID, $messageType, $message, $messageRoom, $userID, $sender, $replyBy)
    {
        $send = $platformClient->pushMessage($UID, $message, $messageType);
        log_message('info', "ข้อความตอบไปที่ลูกค้า Message Room ID $messageRoom->id $this->platform: " . $message);

        if ($send) {

            $this->messageService->saveMessage($messageRoom->id, $userID, $messageType, $message, $this->platform, $sender, $replyBy);

            $customer = $this->customerModel->getCustomerByID($messageRoom->customer_id);

            $this->messageService->sendToWebSocket([
                'messageRoom' => $messageRoom,

                'room_id' => $messageRoom->id,

                'send_by' => $sender,

                'sender_id' => $userID,
                'sender_name' => 'Admin',
                'sender_avatar' => '',

                'platform' => $this->platform,
                'message_type' => $messageType,
                'message' => $message,

                'receiver_id' => hashidsEncrypt($customer->id),
                'receiver_name' => $customer->name,
                'receiver_avatar' => $customer->profile,

                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function prepareWebhookInput($input, $userSocial)
    {
        if (getenv('CI_ENVIRONMENT') === 'development') {
            $input = $this->getMockLineWebhookData();
            $userSocial->line_channel_access_token = 'U7mJfRwa6hGDA32w883lebP2xc+Shhc9go6eb0X5kEsPKWY4Yyb2PdpOoPx7QgMq5Zh+NUB431dT8JB01f/x7qkC6kJ0r8caM4z2dbIdSa3ZcJzTe6mElEG6W9oWQHeW2d9vI/6Ic4jetEUyiL69sY9PbdgDzCFqoOLOYbqAITQ=';
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
        $UID = $customer->uid;

        return $UID;
    }

    private function getMockLineWebhookData()
    {
        // TEXT
        // return json_decode(
        //     '{
        //     "destination": "U3cc700ae815f9f7e37ea930b7b66b2c1",
        //     "events": [
        //         {
        //             "type": "message",
        //             "message": {
        //                 "type": "text",
        //                 "id": "545655842000077303",
        //                 "quoteToken": "cGR08Boi4mUH0aJ2IPb11MNt7guGiglOO3XlF2-JDmUxbTXzexfqvXiiHZ3TPfUwhlheSMslhGk-eQPiGsvziGNo4AXvbDhokDglNTnzR0gB0jIkDvCWQQbgIzVyv6D2P-k6zVQXgYl0tyyWNOFMdA",
        //                 "text": "\u0e23\u0e16"
        //             },
        //             "webhookEventId": "01JJPEBMSMCCAS02MPW7RGXZWQ",
        //             "deliveryContext": {
        //                 "isRedelivery": false
        //             },
        //             "timestamp": 1738067530428,
        //             "source": {
        //                 "type": "user",
        //                 "userId": "U793093e057eb0dcdecc34012361d0217"
        //             },
        //             "replyToken": "d618defc144e43278bf2d6715ef701e2",
        //             "mode": "active"
        //         }
        //     ]
        // }'
        // );

        // return json_decode(
        //     '{
        //     "destination": "U3cc700ae815f9f7e37ea930b7b66b2c1",
        //     "events": [
        //         {
        //             "type": "message",
        //             "message": {
        //                 "type": "text",
        //                 "id": "545655859934921237",
        //                 "quoteToken": "kKZh_dz7HIZBv-ZjBsMUbeKbaGDCyPs9dNff0zcQkGlgmA9l-1PMsg6PLRQtteMGrufJtv2_fdLC0qRSJX_tbu5LQ3gjs4G3QDQJUWwAYiFcvIRV6fD49a_A16xhHvhKv0NTI68dNW0_YG8CWo6l0g",
        //                 "text": "\u0e04\u0e31\u0e19\u0e19\u0e35\u0e49\u0e2d\u0e30\u0e44\u0e23"
        //             },
        //             "webhookEventId": "01JJPEBZHJCEMYFMJXD2WAPNX6",
        //             "deliveryContext": {
        //                 "isRedelivery": false
        //             },
        //             "timestamp": 1738067541066,
        //             "source": {
        //                 "type": "user",
        //                 "userId": "U793093e057eb0dcdecc34012361d0217"
        //             },
        //             "replyToken": "a2edad6d122747cb96c331832e984be5",
        //             "mode": "active"
        //         }
        //     ]
        // }'
        // );

        // Image
        return json_decode(
            '{
            "destination": "U3cc700ae815f9f7e37ea930b7b66b2c1",
            "events": [
                {
                    "type": "message",
                    "message": {
                        "type": "image",
                        "id": "545609780438499330",
                        "quoteToken": "2hTD5_GTcCNcOLqEXWrPFD7wqV1mRtIysYrI8USZF7dAoCJeN-tpaoi8b--yRZvrZecvrEZilPtSL75nC8bTPLh2xb_ZiVe_FmbKXZ7_nF8f_sLWreBKDDNB6j6WOUJBe3iABJv1GVv5FFPQIb-fPA",
                        "contentProvider": {
                            "type": "line"
                        }
                    },
                    "webhookEventId": "01JJNM5SM145NRFJ1V6KYJQMN8",
                    "deliveryContext": {
                        "isRedelivery": false
                    },
                    "timestamp": 1738040075709,
                    "source": {
                        "type": "user",
                        "userId": "U793093e057eb0dcdecc34012361d0217"
                    },
                    "replyToken": "934747a8fd95442f9b8cfcd032d7dd97",
                    "mode": "active"
                }
            ]
        }'
        );

        // Audio
        //         return json_decode(
        //             '{
        //     "destination": "U3cc700ae815f9f7e37ea930b7b66b2c1",
        //     "events": [
        //         {
        //             "type": "message",
        //             "message": {
        //                 "type": "audio",
        //                 "id": "546929768709488706",
        //                 "duration": 7534,
        //                 "contentProvider": {
        //                     "type": "line"
        //                 }
        //             },
        //             "webhookEventId": "01JKD2G7T7HGHNR79HYQYR6E71",
        //             "deliveryContext": {
        //                 "isRedelivery": false
        //             },
        //             "timestamp": 1738826850049,
        //             "source": {
        //                 "type": "user",
        //                 "userId": "U793093e057eb0dcdecc34012361d0217"
        //             },
        //             "replyToken": "bd94a1406d99401e8a6934635ef6e317",
        //             "mode": "active"
        //         }
        //     ]
        // }'
        //         );
    }
}
