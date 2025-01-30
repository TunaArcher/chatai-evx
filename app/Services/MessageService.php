<?php

namespace App\Services;

use App\Models\CustomerModel;
use App\Models\MessageModel;
use App\Models\MessageRoomModel;
use App\Models\TeamMemberModel;
use App\Models\TeamModel;
use App\Models\TeamSocialModel;
use App\Integrations\Line\LineClient;
use App\Integrations\WhatsApp\WhatsAppClient;
use App\Integrations\Facebook\FacebookClient;
use App\Integrations\Instagram\InstagramClient;

class MessageService
{
    private CustomerModel $customerModel;
    private MessageModel $messageModel;
    private MessageRoomModel $messageRoomModel;
    private TeamModel $teamModel;
    private TeamSocialModel $teamSocialModel;
    private TeamMemberModel $teamMemberModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->messageModel = new MessageModel();
        $this->messageRoomModel = new MessageRoomModel();
        $this->teamModel = new TeamModel();
        $this->teamSocialModel = new TeamSocialModel();
        $this->teamMemberModel = new TeamMemberModel();
    }

    // Logic การ Save Message
    public function saveMessage(
        $roomId,
        $senderId,
        $messageType,
        $message,
        $platform,
        $sendBy,
        $replyBy = ''
    ) {

        $this->messageModel->insertMessage([
            'room_id' => $roomId,
            'send_by' => $sendBy,
            'sender_id' => $senderId,
            'message_type' => $messageType,
            'message' => $message,
            'platform' => $platform,
            'reply_by' => $replyBy,
            'is_context' => '1'
        ]);
    }

    // Logic การส่ง Socket
    public function sendToWebSocket(array $data)
    {
        // จัดการ Data ตอนส่งไป WebSocket
        $messageRoom = $data['messageRoom'];
        $userIdLooking = [];
        $teamSocials = $this->teamSocialModel->getTeamSocialByUserSocialID($messageRoom->user_social_id);
        if ($teamSocials) {
            foreach ($teamSocials as $teamSocial) {
                $team = $this->teamModel->getTeamByID($teamSocial->team_id);
                if ($team) {

                    $userIdLooking[] = hashidsEncrypt($team->owner_id);

                    $teamMembers = $this->teamMemberModel->getTeamMemberUserIDByTeamID($team->id);

                    foreach ($teamMembers as $teamMember) {
                        $userIdLooking[] = hashidsEncrypt($teamMember->user_id);
                    }
                }
            }
        } else $userIdLooking[] = hashidsEncrypt($messageRoom->user_id);

        $data['userIdLooking'] = $userIdLooking;

        // ลบข้อมูลโดยใช้ key
        $keyToRemove = "messageRoom";
        if (array_key_exists($keyToRemove, $data)) unset($data[$keyToRemove]);

        $url = getenv('WS_URL'); // URL ของ WebSocket Server
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // แปลงข้อมูลเป็น JSON
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // ไม่ต้องการรับ Response กลับ
        curl_exec($ch);
        curl_close($ch);
    }

    // Logic การสร้างหรือดึงข้อมูลลูกค้า
    public function getOrCreateCustomer($UID, $platform, $userSocial, $name = null)
    {

        $customer = $this->customerModel->getCustomerByUIDAndPlatform($UID, $platform);

        if (!$customer) {

            switch ($platform) {
                case 'Facebook':
                    $faceBookAPI = new FacebookClient(['facebookToken' => $userSocial->fb_token]);
                    $profile = $faceBookAPI->getUserProfileFacebook($UID);
                    $customerID = $this->customerModel->insertCustomer([
                        'platform' => $platform,
                        'uid' => $UID,
                        'name' => $profile->first_name . ' ' . $profile->last_name,
                        'profile' => $profile->profile_pic,
                    ]);
                    break;
                case 'Line':
                    $lineAPI = new LineClient([
                        'userSocialID' => $userSocial->id,
                        'accessToken' => $userSocial->line_channel_access_token,
                        'channelID' => $userSocial->line_channel_id,
                        'channelSecret' => $userSocial->line_channel_secret,
                    ]);
                    $profile = $lineAPI->getUserProfile($UID);

                    $customerID = $this->customerModel->insertCustomer([
                        'platform' => $platform,
                        'uid' => $UID,
                        'name' => $profile->displayName,
                        'profile' => $profile->pictureUrl,
                    ]);
                    break;
                case 'WhatsApp':
                    $customerID = $this->customerModel->insertCustomer([
                        'platform' => $platform,
                        'uid' => $UID,
                        'name' => $name,
                        'profile' => 'https://cdn4.iconfinder.com/data/icons/social-messaging-ui-color-and-shapes-3/177800/129-512.png',
                    ]);
                    break;

                case 'Instagram':
                    $instagramAPI = new InstagramClient(['accessToken' => $userSocial->ig_token]);
                    $profile = $instagramAPI->getUserProfile($UID);
                    $customerID = $this->customerModel->insertCustomer([
                        'platform' => $platform,
                        'uid' => $UID,
                        'name' => $profile ? $profile->name : 'ไม่สามารถระบุยูสได้',
                        'profile' => $profile ?? $profile->profile_picture_url,
                    ]);
                    break;

                case 'Tiktok':
                    $customerID = $this->customerModel->insertCustomer([
                        'platform' => $platform,
                        'uid' => $UID,
                        'name' => $name,
                        'profile' => 'https://cdn4.iconfinder.com/data/icons/social-messaging-ui-color-and-shapes-3/177800/129-512.png',
                    ]);
                    break;
            }

            return $this->customerModel->getCustomerByID($customerID);
        }

        return $customer;
    }

    // Logic การสร้างหรือดึงข้อมูล Message Room
    public function getOrCreateMessageRoom($platform, $customer, $userSocial)
    {
        $messageRoom = $this->messageRoomModel->getMessageRoomByCustomerID($customer->id);

        if (!$messageRoom) {
            $roomId = $this->messageRoomModel->insertMessageRoom([
                'platform' => $platform,
                'user_social_id' => $userSocial->id,
                'user_social_name' => $userSocial->name,
                'customer_id' => $customer->id,
                'user_id' => $userSocial->user_id,
            ]);

            return $this->messageRoomModel->getMessageRoomByID($roomId);
        }

        return $messageRoom;
    }
}
