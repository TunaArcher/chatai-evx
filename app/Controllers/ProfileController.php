<?php

namespace App\Controllers;

use App\Models\SubscriptionModel;
use App\Models\MessageModel;
use App\Models\MessageRoomModel;
use App\Models\TeamMemberModel;
use App\Models\TeamModel;
use App\Models\TeamSocialModel;
use App\Models\UserModel;
use App\Models\UserSocialModel;

class ProfileController extends BaseController
{
    private SubscriptionModel $subscriptionModel;
    private TeamModel $teamModel;
    private TeamSocialModel $teamSocialModel;
    private TeamMemberModel $teamMemberModel;
    private MessageModel $messageModel;
    private MessageRoomModel $messageRoomModel;
    private UserModel $userModel;
    private UserSocialModel $userSocialModel;

    public function __construct()
    {
        $this->messageModel = new MessageModel();
        $this->messageRoomModel = new MessageRoomModel();
        $this->teamModel = new TeamModel();
        $this->teamMemberModel = new TeamMemberModel();
        $this->teamSocialModel = new TeamSocialModel();
        $this->subscriptionModel = new SubscriptionModel();
        $this->userSocialModel = new UserSocialModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $data = [
            'content' => 'profile/index',
            'title' => 'Profile',
            'css_critical' => '',
            'js_critical' => '
                <script src="https://code.jquery.com/jquery-3.7.1.js" crossorigin="anonymous"></script>
                <script src="app/profile.js"></script>
            ',
        ];

        $userOwnerID = session()->get('user_owner_id');
        $data = $userOwnerID
            ? $this->prepareTeamProfileData($data, $userOwnerID)
            : $this->prepareSingleProfileData($data, session()->get('userID'));

        echo view('/app', $data);
    }

    public function getFreeRequestLimit()
    {

        $response = [
            'success' => 0,
            'message' => '',
        ];

        $status = 500;
        try {

            $user = $this->userModel->getUserByID(hashidsDecrypt(session()->get('userID')));

            if (!$user) throw new \Exception('ไม่พบยูส');

            $response = [
                'success' => 1,
                'message' => 'success',
                'free_request_limit' => $user->free_request_limit,
            ];

            $status = 200;

            return $this->response
                ->setStatusCode($status)
                ->setContentType('application/json')
                ->setJSON($response);
        } catch (\Exception $e) {
            return $this->response
                ->setStatusCode($status)
                ->setContentType('application/json')
                ->setJSON($response);
        }
    }

    // -------------------------------------------------------------------------
    // Helper Functions
    // -------------------------------------------------------------------------

    public function prepareSingleProfileData(array $data, string $userID): array
    {
        $userID = hashidsDecrypt($userID);

        $counterMessages = [
            'all' => 0,
            'reply_by_manual' => 0,
            'replay_by_ai' => 0,
        ];

        $userSocials = $this->userSocialModel->getUserSocialByUserID($userID);

        foreach ($userSocials as $userSocial) {
            $messageRooms = $this->messageRoomModel->getMessageRoomByUserID($userID);

            foreach ($messageRooms as $room) {
                $counterMessages = $this->aggregateMessageCounts($counterMessages, $room->id);
            }

            $userSocial->id = hashidsEncrypt($userSocial->id);
        }

        $data['userSocials'] = $userSocials;
        $data['counterMessages'] = $counterMessages;
        $data['teams'] = $this->teamModel->getTeamByOwnerID($userID);
        $data['subscription'] = $this->subscriptionModel->getUserSubscription($userID);

        return $data;
    }

    public function prepareTeamProfileData(array $data, string $userOwnerID): array
    {
        $teams = [];
        $userID = hashidsDecrypt(session()->get('userID'));
        $userOwnerID = hashidsDecrypt($userOwnerID);

        $teamMembers = $this->teamMemberModel->getTeamMemberByUserID($userID);

        foreach ($teamMembers as $teamMember) {
            $team = $this->teamModel->getTeamByID($teamMember->team_id);
            $teams[] = $team;
        }

        $data['teams'] = $teams;
        $data['subscription'] = $this->subscriptionModel->getUserSubscription($userOwnerID);

        return $data;
    }

    private function aggregateMessageCounts(array $counterMessages, string $roomID): array
    {
        $messages = $this->messageModel->getMessageRoomByRoomID($roomID, 'ALL');
        $messagesManual = $this->messageModel->getMessageRoomByRoomID($roomID, 'MANUL');
        $messagesAI = $this->messageModel->getMessageRoomByRoomID($roomID, 'AI');

        $counterMessages['all'] += count($messages);
        $counterMessages['reply_by_manual'] += count($messagesManual);
        $counterMessages['replay_by_ai'] += count($messagesAI);

        return $counterMessages;
    }
}
