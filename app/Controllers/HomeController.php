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

class HomeController extends BaseController
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
            'content' => 'home/index',
            'title' => 'Home',
            'css_critical' => '',
            'js_critical' => '
                <script src="https://code.jquery.com/jquery-3.7.1.js" crossorigin="anonymous"></script>
                <script src="https://cdn.jsdelivr.net/npm/typed.js@2.0.12"></script>
                <script src="app/dashboard.js"></script>
            ',
        ];

        $userOwnerID = session()->get('user_owner_id');
        $data = $userOwnerID
            ? $this->prepareTeamHomePageData($data, $userOwnerID)
            : $this->prepareSingleHomePageData($data, session()->get('userID'));

        echo view('/app', $data);
    }

    public function policy()
    {
        echo view('/policy');
    }

    // -------------------------------------------------------------------------
    // Helper Functions
    // -------------------------------------------------------------------------

    public function prepareTeamHomePageData(array $data, string $userOwnerID): array
    {
        $userID = hashidsDecrypt(session()->get('userID'));
        $userOwnerID = hashidsDecrypt($userOwnerID);

        $data['subscription'] = $this->subscriptionModel->getUserSubscription($userOwnerID);
        $data['counterMessages'] = $this->getMessageCountsByTeam($userID);
        $data['userSocials'] = $this->getUserSocialsByTeam($userID);

        return $data;
    }

    public function prepareSingleHomePageData(array $data, string $userID): array
    {
        $userID = hashidsDecrypt($userID);

        $data['subscription'] = $this->subscriptionModel->getUserSubscription($userID);
        $data['counterMessages'] = $this->getMessageCountsByUser($userID);
        $data['userSocials'] = $this->userSocialModel->getUserSocialByUserID($userID);
        $data['teams'] = $this->teamModel->getTeamByOwnerID($userID);

        return $data;
    }

    private function getMessageCountsByTeam(string $userID): array
    {
        $counterMessages = [
            'all' => 0,
            'reply_by_manual' => 0,
            'replay_by_ai' => 0,
        ];

        $teamMembers = $this->teamMemberModel->getTeamMemberByUserID($userID);

        foreach ($teamMembers as $teamMember) {
            $teamSocials = $this->teamSocialModel->getTeamSocialByTeamID($teamMember->team_id);

            foreach ($teamSocials as $teamSocial) {
                $messageRooms = $this->messageRoomModel->getMessageRoomByUserSocialID($teamSocial->user_social_id);

                foreach ($messageRooms as $room) {
                    $counterMessages = $this->aggregateMessageCounts($counterMessages, $room->id);
                }
            }
        }

        return $counterMessages;
    }

    private function getMessageCountsByUser(string $userID): array
    {
        $counterMessages = [
            'all' => 0,
            'reply_by_manual' => 0,
            'replay_by_ai' => 0,
        ];

        $messageRooms = $this->messageRoomModel->getMessageRoomByUserID($userID);

        foreach ($messageRooms as $room) {
            $counterMessages = $this->aggregateMessageCounts($counterMessages, $room->id);
        }

        return $counterMessages;
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

    private function getUserSocialsByTeam(string $userID): array
    {
        $userSocials = [];
        $teamMembers = $this->teamMemberModel->getTeamMemberByUserID($userID);

        foreach ($teamMembers as $teamMember) {
            $teamSocials = $this->teamSocialModel->getTeamSocialByTeamID($teamMember->team_id);

            foreach ($teamSocials as $teamSocial) {
                $userSocials[] = $teamSocial;
            }
        }

        return $userSocials;
    }
}
