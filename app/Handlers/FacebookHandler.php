<?php

namespace App\Handlers;

use App\Models\UserSocialModel;
use App\Models\MessageRoomModel;
use App\Models\CustomerModel;
use App\Integrations\Facebook\FacebookClient;
use App\Services\MessageService;
use App\Libraries\ChatGPT;
use CodeIgniter\HTTP\Message;

class FacebookHandler
{
    private $platform = 'Facebook';

    private MessageService $messageService;
    private MessageRoomModel $messageRoomModel;
    private UserSocialModel $userSocialModel;
    private CustomerModel $customerModel;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
        $this->messageRoomModel = new MessageRoomModel();
        $this->userSocialModel = new UserSocialModel();
        $this->customerModel = new CustomerModel();
    }

    public function handleWebhook($input, $userSocial): void
    {
        // ข้อมูล Mock สำหรับ Development
        if (getenv('CI_ENVIRONMENT') == 'development') $input = $this->getMockFacebookWebhookData();

        // ดึงข้อมูลเหตุการณ์จาก facebook
        $message = $input->entry[0]->messaging[0]->message->text ?? null;
        $UID = $input->entry[0]->messaging[0]->sender->id ?? null;

        // log_message('info', 'check uid: ' . json_encode($UID, JSON_PRETTY_PRINT));

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

    public function handleReplyByManual($input, $customer)
    {
        $userID = session()->get('userID');
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
        $GPTToken = getenv('GPT_TOKEN');
        // CONNECT TO GPT
        $userID = session()->get('userID');
        $message = $input->entry[0]->messaging[0]->message->text ?? null;
        $UID = $input->entry[0]->messaging[0]->sender->id ?? null;
    
        $chatGPT = new ChatGPT([
            'GPTToken' => $GPTToken
        ]);

        $messageReplyToCustomer = $chatGPT->askChatGPT($message);
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

    private function getMockFacebookWebhookData()
    {
        return json_decode(
            '{
  "entry": [
        {
            "time": 1733735932500,
            "id": "436618552864074",
            "messaging": [
                {
                    "sender": {
                        "id": "9158866310814762"
                    },
                    "recipient": {
                        "id": "436618552864074"
                    },
                    "timestamp": 1733735447211,
                    "message": {
                        "mid": "m_ixUxEqTYyfCqkYFXfSTDivX7oe5Mk-1qL9AMvuUqedICKaaOOHzQGAHbfmoc3zQ3xjcyfJlUrF30SVsi6ww7Sw",
                        "text": "AAA"
                    }
                }
            ]
        }
    ]
}'
        );
    }
}
