<?php

namespace App\Handlers;

use App\Models\UserSocialModel;
use App\Models\MessageRoomModel;
use App\Models\CustomerModel;
use App\Integrations\Facebook\FacebookClient;
use App\Services\MessageService;
use App\Libraries\ChatGPT;
use App\Models\UserModel;
use CodeIgniter\HTTP\Message;

class FacebookHandler
{
    private $platform = 'Facebook';

    private MessageService $messageService;
    private MessageRoomModel $messageRoomModel;
    private UserSocialModel $userSocialModel;
    private CustomerModel $customerModel;
    private UserModel $userModel;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
        $this->messageRoomModel = new MessageRoomModel();
        $this->userSocialModel = new UserSocialModel();
        $this->customerModel = new CustomerModel();
        $this->userModel = new UserModel();
    }

    public function handleWebhook($input, $userSocial): void
    {
        // ข้อมูล Mock สำหรับ Development
        $input = $this->getMockFacebookWebhookData();

        // ดึงข้อมูล Platform ที่ Webhook เข้ามา
        // ตรวจสอบว่าเป็น Message ข้อความ หรือ รูปภาพ และจัดการ
        $message = $this->processMessage($input, $userSocial);

        // ตรวจสอบหรือสร้างลูกค้า
        $customer = $this->messageService->getOrCreateCustomer($message['UID'], $this->platform, $userSocial);

        // ตรวจสอบหรือสร้างห้องสนทนา
        $messageRoom = $this->messageService->getOrCreateMessageRoom($this->platform, $customer, $userSocial);

        // บันทึกข้อความในฐานข้อมูล
        $this->messageService->saveMessage($messageRoom->id, $customer->id, $message['type'], $message['content'], $this->platform, 'Customer');

        // ส่งข้อความไปยัง WebSocket Server
        $this->messageService->sendToWebSocket([
            'messageRoom' => $messageRoom,

            'room_id' => $messageRoom->id,

            'send_by' => 'Customer',

            'sender_id' => $customer->id,
            'sender_name' => $customer->name,
            'sender_avatar' => $customer->profile,

            'platform' => $this->platform,
            'message_type' => $message['type'],
            'message' => $message['content'],

            'receiver_id' => hashidsEncrypt($messageRoom->user_id),
            'receiver_name' => 'Admin',
            'receiver_avatar' => '',

            'created_at' => date('Y-m-d H:i:s'),
        ]);

        
    }

    public function handleReplyByManual($input, $customer)
    {
        $userID = hashidsDecrypt(session()->get('userID'));
        $messageReplyToCustomer = $input->message;
        $messageRoom = $this->messageRoomModel->getMessageRoomByID($input->room_id);

        // ข้อมูล Mock สำหรับ Development
        if (getenv('CI_ENVIRONMENT') == 'development') {
            $UID = '9158866310814762';
            $facebookToken = 'EAAOQeQ3h77gBO3i4jZByjigIFMPNOEbEZBtT430FjEm1QWNqXM3Y2yrrVfI4ZCkPEm9bPu6YeX5hnLr8s1Rg8QfEMAmj6nZAoZAnxgrM5cgE4jZBD9CZAULKS9BxCJTh4xHhHUH1W1gS8GEyaXxMHM9QpnZAjZCKRzpDMIBqeqQC89IQBwfemAqft2MjqjZArAfwfWXQZDZD';
        } else {
            $userSocial = $this->userSocialModel->getUserSocialByID($messageRoom->user_social_id);
            $UID = $customer->uid;
            $facebookToken = $userSocial->fb_token;

            // log_message('info', 'uid Facebook: ' . json_encode($UID, JSON_PRETTY_PRINT));
        }

        $facebookAPI = new FacebookClient([
            'facebookToken' => $facebookToken
        ]);
        $send = $facebookAPI->pushMessage($UID, $messageReplyToCustomer);
        log_message('info', 'ข้อความตอบไปที่ลูกค้า Facebook: ' . json_encode($messageReplyToCustomer, JSON_PRETTY_PRINT));

        if ($send) {

            // บันทึกข้อความในฐานข้อมูล
            $this->messageService->saveMessage($messageRoom->id, $userID, $messageReplyToCustomer, $this->platform, 'Admin', 'MANUAL');

            // ส่งข้อความไปยัง WebSocket Server
            $this->messageService->sendToWebSocket([
                'messageRoom' => $messageRoom,

                'room_id' => $messageRoom->id,

                'send_by' => 'Admin',
                'sender_id' => $userID,
                'sender_name' => 'Admin',
                'sender_avatar' => '',

                'platform' => $this->platform,
                'message' => $messageReplyToCustomer,

                'receiver_id' => hashidsEncrypt($customer->id),
                'receiver_name' => $customer->name,
                'receiver_avatar' => $customer->profile,

                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function handleReplyByAI($input, $userSocial)
    {
        $GPTToken = getenv('GPT_TOKEN');
        // CONNECT TO GPT
        $userID = hashidsDecrypt(session()->get('userID'));
        $message = $input->entry[0]->messaging[0]->message->text ?? null;
        $UID = $input->entry[0]->messaging[0]->sender->id ?? null;

        $chatGPT = new ChatGPT([
            'GPTToken' => $GPTToken
        ]);

        $dataMessage = $this->userModel->getMessageTraningByID($userSocial->user_id);
        $messageReplyToCustomer = $chatGPT->askChatGPT($message, $dataMessage->message);
        $customer = $this->customerModel->getCustomerByUIDAndPlatform($UID, $this->platform);
        $messageRoom = $this->messageRoomModel->getMessageRoomByCustomerID($customer->id);

        // ข้อมูล Mock สำหรับ Development
        if (getenv('CI_ENVIRONMENT') == 'development') {
            $UID = '9158866310814762';
            $facebookToken = 'EAAOQeQ3h77gBO3i4jZByjigIFMPNOEbEZBtT430FjEm1QWNqXM3Y2yrrVfI4ZCkPEm9bPu6YeX5hnLr8s1Rg8QfEMAmj6nZAoZAnxgrM5cgE4jZBD9CZAULKS9BxCJTh4xHhHUH1W1gS8GEyaXxMHM9QpnZAjZCKRzpDMIBqeqQC89IQBwfemAqft2MjqjZArAfwfWXQZDZD';
        } else {
            $userSocial = $this->userSocialModel->getUserSocialByID($messageRoom->user_social_id);
            $UID = $UID;
            $facebookToken = $userSocial->fb_token;
        }

        $facebookAPI = new FacebookClient([
            'facebookToken' => $facebookToken
        ]);

        $send = $facebookAPI->pushMessage($UID, $messageReplyToCustomer);
        log_message('info', 'ข้อความตอบไปที่ลูกค้า Facebook: ' . json_encode($messageReplyToCustomer, JSON_PRETTY_PRINT));

        if ($send) {

            // บันทึกข้อความในฐานข้อมูล
            $this->messageService->saveMessage($messageRoom->id, $userID, $messageReplyToCustomer, $this->platform, 'Admin', 'AI');

            // ส่งข้อความไปยัง WebSocket Server
            $this->messageService->sendToWebSocket([
                'messageRoom' => $messageRoom,

                'room_id' => $messageRoom->id,

                'send_by' => 'Admin',
                'sender_id' => $userID,
                'sender_name' => 'Admin',
                'sender_avatar' => '',

                'platform' => $this->platform,
                'message' => $messageReplyToCustomer,

                'receiver_id' => hashidsEncrypt($customer->user_id),
                'receiver_name' => $customer->name,
                'receiver_avatar' => $customer->profile,

                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
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

            $messageType = 'image';

            $attachments = [];

            foreach ($inputMessage->attachments as $attachment) {

                if ($attachment->type === 'image') {

                    $url = $attachment->payload->url;

                    $fileContent = fetchFileFromWebhook($url);

                    // ตั้งชื่อไฟล์แบบสุ่ม
                    $fileName = uniqid('facebook_') . '.jpg';

                    // อัปโหลดไปยัง Spaces
                    $message = uploadToSpaces($fileContent, $fileName);

                    $attachments[] = $message;
                }
            }

            $message = json_encode($attachments);
        }

        return [
            'UID' => $UID,
            'type' => $messageType,
            'content' => $message,
        ];
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
        //                         "id": "9158866310814762"
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
