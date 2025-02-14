<?php

namespace App\Handlers;

use App\Integrations\WhatsApp\WhatsAppClient;
use App\Libraries\ChatGPT;
use App\Models\CustomerModel;
use App\Models\MessageModel;
use App\Models\MessageRoomModel;
use App\Models\UserModel;
use App\Models\UserSocialModel;
use App\Services\MessageService;

class WhatsAppHandler
{
    private $platform = 'WhatsApp';

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
        // ตรวจสอบว่าเป็น Message ข้อความ หรือ รูปภาพ และจัดการ
        $message = $this->processMessage($input, $userSocial);

        // ตรวจสอบหรือสร้างลูกค้า
        $customer = $this->messageService->getOrCreateCustomer($message['UID'], $this->platform, $userSocial, $message['name'] ?? null);

        // ตรวจสอบหรือสร้างห้องสนทนา
        $messageRoom = $this->messageService->getOrCreateMessageRoom($this->platform, $customer, $userSocial);

        // บันทึกข้อความและส่งต่อ WebSocket
        $this->processIncomingMessage(
            $messageRoom,
            $customer,
            $message['type'],
            $message['content'],
            'Customer'
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
            $messageType, // fix เป็น Text ไปก่อน
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
            session()->get('userID'),
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
                    $imageUrl .=  $message->message . ',';
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

    // private function processMessage($input, $userSocial)
    // {
    //     $entry = $input->entry[0] ?? null;
    //     $changes = $entry->changes[0] ?? null;
    //     $value = $changes->value ?? null;
    //     $messageObject = $value->messages[0] ?? null;
    //     $contact = $value->contacts[0] ?? null;

    //     $UID = $messageObject->from ?? null;
    //     $messageType = $messageObject->type ?? 'text';
    //     $name = $contact->profile->name ?? null;

    //     switch ($messageType) {

    //             // เคสข้อความ
    //         case 'text':
    //             $messageContent = $messageObject->text->body ?? null;
    //             break;

    //         default:
    //             $messageContent = null;
    //     }

    //     return [
    //         'UID' => $UID,
    //         'type' => $messageType,
    //         'content' => $messageContent,
    //         'name' => $name,
    //     ];
    // }

    private function processMessage($input, $userSocial)
    {
        $entry = $input->entry[0] ?? null;
        $changes = $entry->changes[0] ?? null;
        $value = $changes->value ?? null;
        $messageObject = $value->messages[0] ?? null;
        $contact = $value->contacts[0] ?? null;

        $UID = $messageObject->from ?? null;
        $messageType = $messageObject->type ?? 'text';
        $name = $contact->profile->name ?? null;
        $messageContent = null;

        switch ($messageType) {
                // เคสข้อความ
            case 'text':
                $messageType = 'text';
                $messageContent = $messageObject->text->body ?? null;
                break;

                // เคสรูปภาพ
            case 'image':
                $messageType = 'image';
                $messageId = $messageObject->image->id ?? null;
                $whatsappAccessToken = $userSocial->whatsapp_access_token;

                $url = "https://graph.facebook.com/v21.0/{$messageId}";
                $headers = ["Authorization: Bearer {$whatsappAccessToken}"];

                // ดึงข้อมูลไฟล์จาก Webhook WhatsApp
                $fileContent = fetchFileFromWebhook($url, $headers);

                // ตั้งชื่อไฟล์แบบสุ่ม
                $fileName = uniqid('wa_') . '.jpg';

                // อัปโหลดไปยัง Spaces
                $messageContent = uploadToSpaces(
                    $fileContent,
                    $fileName,
                    $messageType,
                    $this->platform
                );
                break;

                // เคสเสียง
            case 'audio':
                $messageType = 'audio';
                $messageId = $messageObject->audio->id ?? null;
                $whatsappAccessToken = $userSocial->whatsapp_access_token;

                $url = "https://graph.facebook.com/v21.0/{$messageId}";
                $headers = ["Authorization: Bearer {$whatsappAccessToken}"];

                // ดึงข้อมูลไฟล์จาก Webhook WhatsApp
                $fileContent = fetchFileFromWebhook($url, $headers);

                // ตั้งชื่อไฟล์แบบสุ่ม
                $fileName = uniqid('wa_') . '.m4a';

                // อัปโหลดไปยัง Spaces
                $messageContent = uploadToSpaces(
                    $fileContent,
                    $fileName,
                    $messageType,
                    $this->platform
                );
                break;

            default:
                $messageContent = null;
        }

        return [
            'UID' => $UID,
            'type' => $messageType,
            'content' => $messageContent,
            'name' => $name,
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
            $sender
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

    private function sendMessageToPlatform($platformClient, $UID, $message, $messageRoom, $userID, $sender, $replyBy)
    {
        $send = $platformClient->pushMessage($UID, $message);
        log_message('info', "ข้อความตอบไปที่ลูกค้า Message Room ID $messageRoom->id $this->platform: " . json_encode($message, JSON_PRETTY_PRINT));

        if ($send) {

            $this->messageService->saveMessage($messageRoom->id, $userID, $message, $this->platform, $sender, $replyBy);

            $customer = $this->customerModel->getCustomerByID($messageRoom->customer_id);

            $this->messageService->sendToWebSocket([
                'messageRoom' => $messageRoom,

                'room_id' => $messageRoom->id,

                'send_by' => $sender,

                'sender_id' => $userID,
                'sender_name' => 'Admin',
                'sender_avatar' => '',

                'platform' => $this->platform,
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
            $input = $this->getMockWhatsAppWebhookData();
            $userSocial->whatsapp_phone_number_id = '513951735130592';
            $userSocial->whatsapp_token = 'EAAPwTXFKRgoBO3m1wcmZBUa92023EjuTrvFe5rAHKSO9se0pPoMyeQgZCxyvu3dQGLj8wyM0lXN8iuyvtzUCYinTRnfTKRrfYZCQYQ8EEdwlrB0rT6PjIOAlZCLN0dxernIo4SyWRY0p4IjsWFGpr34Y4KSMTUqwWVVFFWoUsvbxMB7NwTcZBvxd67nsW42ZA3rtrvtVFZAHG6VWfkiKMZB3DAqbpkUZD';
        }

        return $input;
    }

    private function preparePlatformClient($messageRoom)
    {
        $userSocial = $this->userSocialModel->getUserSocialByID($messageRoom->user_social_id);

        // TODO:: REFACTOR
        $user = $this->userModel->getUserByID($userSocial->user_id);

        return new WhatsAppClient([
            'phoneNumberID' => $userSocial->whatsapp_phone_number_id,
            // 'whatsAppToken' => $userSocial->whatsapp_token
            'whatsAppToken' => $user->whatsapp_access_token
        ]);
    }

    private function getCustomerUID($messageRoom)
    {
        if (getenv('CI_ENVIRONMENT') == 'development') return '66611188669';

        $customer = $this->customerModel->getCustomerByID($messageRoom->customer_id);
        $UID = $customer->uid;

        return $UID;
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
