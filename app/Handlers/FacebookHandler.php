<?php

namespace App\Handlers;

use App\Integrations\Facebook\FacebookClient;
use App\Libraries\ChatGPT;
use App\Models\CustomerModel;
use App\Models\MessageModel;
use App\Models\MessageRoomModel;
use App\Models\UserModel;
use App\Models\UserSocialModel;
use App\Services\MessageService;

class FacebookHandler
{
    private $platform = 'Facebook';

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
        if (getenv('CI_ENVIRONMENT') === 'development') {
            // ข้อมูล Mock สำหรับ Development
            $input = $this->getMockFacebookWebhookData();
        }

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
        $dataMessage = $dataMessage ? $dataMessage->message : 'you are assistance';

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
        $contextText = '';
        $imageUrl = '';

        foreach ($messages as $message) {
            switch ($message->message_type) {
                case 'text':
                    $contextText .= $message->message . ' ';
                    break;
                case 'image':
                    $message_fix =  str_replace('["', "", $message->message);
                    $message_fix =  str_replace('"]', "", $message_fix);
                    $imageUrl .= trim($message_fix, "") . ',';
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

    private function processMessage($input)
    {
        $UID = $input->entry[0]->messaging[0]->sender->id ?? null;
        $inputMessage = $input->entry[0]->messaging[0]->message;

        // เคสข้อความ
        if (isset($inputMessage->text)) {
            $messageType = 'text';
            $message = $inputMessage->text;
        }

        // เคสรูปภาพหรือ attachment อื่น ๆ
        else if (isset($inputMessage->attachments)) {

            $messageType = $inputMessage->attachments[0]->type;

            switch ($messageType) {

                    // เคสรูปภาพ
                case 'image':
                    $messageType = 'image';
                    $attachments = $inputMessage->attachments ?? [];
                    $uploadedImages = [];

                    foreach ($attachments as $attachment) {

                        if ($attachment->type === 'image' && $attachment->payload->url) {

                            $fileUrl = $attachment->payload->url;

                            $fileContent = fetchFileFromWebhook($fileUrl);

                            // ตั้งชื่อไฟล์แบบสุ่ม
                            $fileName = uniqid('facebook_') . '.jpg';

                            // อัปโหลดไปยัง Spaces
                            $message = uploadToSpaces(
                                $fileContent,
                                $fileName,
                                $messageType,
                                $this->platform
                            );

                            $uploadedImages[] = $message;
                        }
                    }

                    $message = json_encode($uploadedImages, JSON_UNESCAPED_SLASHES);

                    break;

                    // เคสเสียง
                case 'audio':
                    $messageType = 'audio';

                    $attachmentUrl = $inputMessage->attachments[0]->payload->url;

                    // ดึงข้อมูลไฟล์จาก Facebook Messenger
                    $fileContent = fetchFileFromWebhook($attachmentUrl);

                    // ตั้งชื่อไฟล์แบบสุ่ม
                    $fileName = uniqid('facebook_') . '.mp4';

                    // อัปโหลดไปยัง DigitalOcean Spaces
                    $message = uploadToSpaces(
                        $fileContent,
                        $fileName,
                        $messageType,
                        $this->platform
                    );

                    break;
            }
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

    private function preparePlatformClient($messageRoom)
    {
        $userSocial = $this->userSocialModel->getUserSocialByID($messageRoom->user_social_id);

        if (getenv('CI_ENVIRONMENT') == 'development') {
            $facebookToken = 'EAAOQeQ3h77gBO3i4jZByjigIFMPNOEbEZBtT430FjEm1QWNqXM3Y2yrrVfI4ZCkPEm9bPu6YeX5hnLr8s1Rg8QfEMAmj6nZAoZAnxgrM5cgE4jZBD9CZAULKS9BxCJTh4xHhHUH1W1gS8GEyaXxMHM9QpnZAjZCKRzpDMIBqeqQC89IQBwfemAqft2MjqjZArAfwfWXQZDZD';
        } else {
            $userSocial = $this->userSocialModel->getUserSocialByID($messageRoom->user_social_id);
            $facebookToken = $userSocial->fb_token;
        }

        return new FacebookClient([
            'facebookToken' => $facebookToken
        ]);
    }

    private function getCustomerUID($messageRoom)
    {
        if (getenv('CI_ENVIRONMENT') == 'development') return '9158866310814762';

        $customer = $this->customerModel->getCustomerByID($messageRoom->customer_id);
        $UID = $customer->uid;

        return $UID;
    }

    private function getMockFacebookWebhookData()
    {
        //         return json_decode(
        //             '{
        //   "entry": [
        //         {
        //             "time": 1733735932500,
        //             "id": "436618552864074",
        //             "messaging": [
        //                 {
        //                     "sender": {
        //                         "id": "6953738848083835"
        //                     },
        //                     "recipient": {
        //                         "id": "436618552864074"
        //                     },
        //                     "timestamp": 1733735447211,
        //                     "message": {
        //                         "mid": "m_ixUxEqTYyfCqkYFXfSTDivX7oe5Mk-1qL9AMvuUqedICKaaOOHzQGAHbfmoc3zQ3xjcyfJlUrF30SVsi6ww7Sw",
        //                         "text": "AAA"
        //                     }
        //                 }
        //             ]
        //         }
        //     ]
        // }'
        //         );

        //         return json_decode(
        //             '{
        //     "object": "page",
        //     "entry": [
        //         {
        //             "time": 1738040135705,
        //             "id": "1741273556202429",
        //             "messaging": [
        //                 {
        //                     "sender": {
        //                         "id": "6953738848083835"
        //                     },
        //                     "recipient": {
        //                         "id": "1741273556202429"
        //                     },
        //                     "timestamp": 1738040129708,
        //                     "message": {
        //                         "mid": "m_aSADP4bQW7FkvDgbzRLoUNXamZvwMpCP7Bgd2yUITvyNDujiyCF9cFjIyL_uJ-lkHQ0L95aWOv3MZ6fhsItmjQ",
        //                         "attachments": [
        //                             {
        //                                 "type": "image",
        //                                 "payload": {
        //                                     "url": "https:\/\/scontent.xx.fbcdn.net\/v\/t1.15752-9\/474954864_1331807411336011_8479358143271413977_n.jpg?_nc_cat=105&ccb=1-7&_nc_sid=fc17b8&_nc_ohc=PH9kbPuLCZYQ7kNvgHEmeUi&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.xx&oh=03_Q7cD1gHZeiGntWRlhzrtW4HDRazVhhUpDiqZ78nm6mxPDzmwMw&oe=67BFC486"
        //                                 }
        //                             }
        //                         ]
        //                     }
        //                 }
        //             ]
        //         }
        //     ]
        // }'
        //         );

        return json_decode(
            '{
"object": "page",
"entry": [
{
    "time": 1738040135705,
    "id": "1741273556202429",
    "messaging": [
        {
            "sender": {
                "id": "6953738848083835"
            },
            "recipient": {
                "id": "1741273556202429"
            },
            "timestamp": 1738040129708,
            "message": {
                "mid": "m_aSADP4bQW7FkvDgbzRLoUNXamZvwMpCP7Bgd2yUITvyNDujiyCF9cFjIyL_uJ-lkHQ0L95aWOv3MZ6fhsItmjQ",
                "attachments": [
                            {
                                "type": "image",
                                "payload": {
                                    "url": "https:\/\/scontent.xx.fbcdn.net\/v\/t1.15752-9\/474861894_1813114326173023_3716877892192802114_n.jpg?_nc_cat=111&ccb=1-7&_nc_sid=fc17b8&_nc_ohc=s24fni3aNXAQ7kNvgEpZ7Mn&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.xx&oh=03_Q7cD1gG2waO6bdOfgDvziOTEK1ttJR2jIwff3WDH8HmldYTICg&oe=67C028C5"
                                }
                            },
                            {
                                "type": "image",
                                "payload": {
                                    "url": "https:\/\/scontent.xx.fbcdn.net\/v\/t1.15752-9\/474954864_1331807411336011_8479358143271413977_n.jpg?_nc_cat=105&ccb=1-7&_nc_sid=fc17b8&_nc_ohc=PH9kbPuLCZYQ7kNvgH7FX5s&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.xx&oh=03_Q7cD1gHqEYkJyMyPDtx2noXKiHDCTJ8kwRRU2fu6MPEQE3QVsg&oe=67BFFCC6"
                                }
                            }
                        ]
            }
        }
    ]
}
]
}'
        );
    }
}
