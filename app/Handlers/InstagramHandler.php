<?php

namespace App\Handlers;

use App\Integrations\Instagram\InstagramClient;
use App\Libraries\ChatGPT;
use App\Models\CustomerModel;
use App\Models\MessageModel;
use App\Models\MessageRoomModel;
use App\Models\UserModel;
use App\Models\UserSocialModel;
use App\Services\MessageService;

class InstagramHandler
{
    private $platform = 'Instagram';

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
        $customer = $this->messageService->getOrCreateCustomer($message['UID'], $this->platform, $userSocial);

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

    // private function processMessage($input, $userSocial)
    // {
    //     $entry = $input->entry[0] ?? null;
    //     $messaging = $entry->messaging[0] ?? null;
    //     $UID = $messaging->sender->id ?? null;
    //     $messageType = $messaging->message->attachments[0]->type ?? 'text';

    //     switch ($messageType) {
    //             // เคสข้อความ
    //         case 'text':
    //             $messageContent = $messaging->message->text ?? '';
    //             break;

    //             // เคสรูปภาพหรือ attachment อื่น ๆ
    //         case 'image':
    //             $attachment = $messaging->message->attachments[0] ?? null;
    //             if ($attachment && isset($attachment->payload->url)) {
    //                 $fileUrl = $attachment->payload->url;
    //                 $fileName = uniqid('instagram_') . '.jpg';
    //                 $messageContent = uploadToSpaces(fetchFileFromWebhook($fileUrl), $fileName);
    //                 $messageContent = json_encode($messageContent);
    //             } else {
    //                 $messageContent = '';
    //             }
    //             break;

    //         default:
    //             $messageContent = '';
    //     }

    //     return [
    //         'UID' => $UID,
    //         'type' => $messageType,
    //         'content' => $messageContent,
    //     ];
    // }

    private function processMessage($input, $userSocial)
    {
        $entry = $input->entry[0] ?? null;
        $messaging = $entry->messaging[0] ?? null;
        $UID = $messaging->sender->id ?? null;
        $messageType = $messaging->message->attachments[0]->type ?? ($messaging->message->text ? 'text' : '');
        $messageContent = '';

        switch ($messageType) {

                // เคสข้อความ
            case 'text':

                $messageType = 'text';

                $messageContent = $messaging->message->text ?? '';

                break;

                // เคสรูปภาพ (รองรับหลายภาพ)
            case 'image':

                $messageType = 'image';

                $attachments = $messaging->message->attachments ?? [];

                $uploadedImages = [];

                foreach ($attachments as $attachment) {

                    if ($attachment->type === 'image' && isset($attachment->payload->url)) {

                        $fileUrl = $attachment->payload->url;

                        $fileContent = fetchFileFromWebhook($fileUrl);

                        // ตั้งชื่อไฟล์แบบสุ่ม
                        $fileName = uniqid('instagram_') . '.jpg';

                        // อัปโหลดไปยัง Spaces
                        $uploadedImages[] = uploadToSpaces($fileContent, $fileName, $messageType, $this->platform);
                    }
                }
                $messageContent = json_encode($uploadedImages, JSON_UNESCAPED_SLASHES);
                break;

                // เคสเสียง
            case 'audio':

                $messageType = 'audio';

                $attachment = $messaging->message->attachments[0] ?? null;

                if ($attachment && isset($attachment->payload->url)) {

                    $fileUrl = $attachment->payload->url;

                    $fileContent = fetchFileFromWebhook($fileUrl);

                    // ตั้งชื่อไฟล์แบบสุ่ม
                    $fileName = uniqid('instagram_') . '.m4a';

                    // อัปโหลดไปยัง Spaces
                    $messageContent = uploadToSpaces($fileContent, $fileName, $messageType, $this->platform);
                }

                break;

            default:
                $messageContent = '';
        }

        return [
            'UID' => $UID,
            'type' => $messageType,
            'content' => $messageContent,
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
        if (getenv('CI_ENVIRONMENT') == 'development') return '1090651699462050';

        $customer = $this->customerModel->getCustomerByID($messageRoom->customer_id);
        $UID = $customer->uid;

        return $UID;
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
