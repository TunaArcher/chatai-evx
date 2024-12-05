<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\MessageModel;
use App\Models\MessageRoomModel;
use App\Models\UserSocialModel;
use App\Models\UserModel;

use App\Libraries\Line;

use CodeIgniter\HTTP\ResponseInterface;

class WebhookController extends BaseController
{
    private CustomerModel $customerModel;
    private MessageModel $messageModel;
    private MessageRoomModel $messageRoomModel;
    private UserModel $userModel;
    private UserSocialModel $userSocialModel;


    /**
     * Constructor สำหรับเตรียม Model ที่จำเป็น
     */
    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->messageModel = new MessageModel();
        $this->messageRoomModel = new MessageRoomModel();
        $this->userModel = new UserModel();
        $this->userSocialModel = new UserSocialModel();
    }


    public function verifyWebhook()
    {
        $hubMode = $this->request->getGet('hub.mode');
        $hubVerifyToken = $this->request->getGet('hub.verify_token');
        $hubChallenge = $this->request->getGet('hub.challenge');

        // ตรวจสอบเงื่อนไข
        if ($hubMode === 'subscribe' && $hubVerifyToken === 'HAPPY') {
            // ส่ง hub.challenge กลับ
            return $this->response->setStatusCode(ResponseInterface::HTTP_OK)->setBody($hubChallenge);
        } else {
            // ส่งสถานะ 400 หากการตรวจสอบไม่ผ่าน
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
    }

    /**
     * ฟังก์ชันสำหรับรับข้อมูล Webhook จากแพลตฟอร์มต่าง ๆ
     * - ตรวจสอบข้อมูล
     * - บันทึกข้อความในฐานข้อมูล
     * - ส่งข้อความไปยัง WebSocket Server
     */
    public function webhook($userSocialID)
    {
        $input = $this->request->getJSON();

        $userSocial = $this->userSocialModel->getUserSocialByID(hashidsDecrypt($userSocialID));

        $platform = $userSocial->platform;

        switch ($platform) {
            case 'Facebook':

                break;


            case 'Line':
                $this->handleWebHookLine($input, $userSocial);
                break;

            case 'WhatsApp':
                break;

            case 'Instagram':
                break;

            case 'Tiktok':
                break;
        }

        return $this->response->setJSON(['status' => 'success']);
    }

    public function handleWebHookLine($input, $userSocial)
    {
        $platform = 'Line';

        // ข้อมูล Mock สำหรับ Development
        if (getenv('CI_ENVIRONMENT') == 'development') {
            $input = $this->getMockLineWebhookData(); // ใช้ข้อมูล Mock ใน Development
            $channelAccessToken = 'UUvglmk7qWbUBSAzM2ThjtAtV+8ipnI1KabsWobuQt8VqFgizLGi91+eVfpZ86i9YRU/oWrmHSBFtACvAwZ/Z6rynrfHU4tWEQi6Yi/HhHzBjCeD5pMdPODqLaEbfCO5bX7rlAbD5swrrhQPljjhTgdB04t89/1O/w1cDnyilFU=';
        } else {
            $channelAccessToken = $userSocial->line_channel_access_token; // ใช้ข้อมูลจริงใน Production
        }

        // ดึงข้อมูลเหตุการณ์จาก Line API
        $event = $input->events[0];
        $UID = $event->source->userId;
        $message = $event->message->text;

        // ตรวจสอบหรือสร้างลูกค้า
        $customer = $this->webHookLineGetOrCreateCustomer($UID, $platform, $channelAccessToken);

        // ตรวจสอบหรือสร้างห้องสนทนา
        $messageRoom = $this->getOrCreateMessageRoom($customer, $userSocial);

        // บันทึกข้อความลงฐานข้อมูล
        $this->saveMessage($messageRoom->id, $customer->id, $message, $platform);

        // ส่งข้อความไปยัง WebSocket Server
        sendMessageToWebSocket([
            'room_id' => $messageRoom->id,
            'send_by' => 'Customer',
            'sender_id' => $customer->id,
            'message' => $message,
            'platform' => $platform,
            'sender_name' => $customer->name,
            'created_at' => date('Y-m-d H:i:s'),
            'sender_avatar' => $customer->profile
        ]);
    }

    // ตรวจสอบหรือสร้างลูกค้าใหม่ในระบบ
    private function webHookLineGetOrCreateCustomer($UID, $platform, $channelAccessToken)
    {
        $customer = $this->customerModel->getCustomerByUIDAndPlatform($UID, $platform);

        if (!$customer) {
            $lineAPI = new Line(['channelAccessToken' => $channelAccessToken]);
            $profile = $lineAPI->getProfile($UID);

            $customerID = $this->customerModel->insertCustomer([
                'platform' => $platform,
                'uid' => $UID,
                'name' => $profile->displayName,
                'profile' => $profile->pictureUrl,
            ]);

            return $this->customerModel->getCustomerByID($customerID);
        }

        return $customer;
    }

    // -----------------------------------------------------------------------------
    // Helper
    // -----------------------------------------------------------------------------

    // ตรวจสอบหรือสร้างห้องสนทนาใหม่
    private function getOrCreateMessageRoom($customer, $userSocial)
    {
        $messageRoom = $this->messageRoomModel->getMessageRoomByCustomerID($customer->id);

        if (!$messageRoom) {
            $roomID = $this->messageRoomModel->insertMessageRoom([
                'platform' => 'Line',
                'user_social_id' => $userSocial->id,
                'user_social_name' => $userSocial->name,
                'customer_id' => $customer->id,
                'user_id' => $userSocial->user_id,
            ]);

            return $this->messageRoomModel->getMessageRoomByID($roomID);
        }

        return $messageRoom;
    }

    // บันทึกข้อความลงฐานข้อมูล
    private function saveMessage($roomID, $customerID, $message, $platform)
    {
        $this->messageModel->insertMessage([
            'room_id' => $roomID,
            'send_by' => 'Customer',
            'sender_id' => $customerID,
            'message' => $message,
            'platform' => $platform,
        ]);
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
