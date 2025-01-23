<?php

namespace App\Controllers;

use App\Models\SubscriptionModel;
use App\Models\MessageModel;
use App\Models\MessageRoomModel;
use App\Models\TeamModel;
use App\Models\TeamSocialModel;
use App\Models\UserModel;
use App\Models\UserSocialModel;

class HomeController extends BaseController
{
    private SubscriptionModel $subscriptionModel;
    private TeamModel $teamModel;
    private TeamSocialModel $teamSocialModel;
    private MessageModel $messageModel;
    private MessageRoomModel $messageRoomModel;
    private UserModel $userModel;
    private UserSocialModel $userSocialModel;

    public function __construct()
    {
        $this->messageModel = new MessageModel();
        $this->messageRoomModel = new MessageRoomModel();
        $this->teamModel = new TeamModel();
        $this->teamSocialModel = new TeamSocialModel();
        $this->subscriptionModel = new SubscriptionModel();
        $this->userSocialModel = new UserSocialModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $data['content'] = 'home/index';
        $data['title'] = 'Home';
        $data['css_critical'] = '';
        $data['js_critical'] = ' 
            <script src="https://code.jquery.com/jquery-3.7.1.js" crossorigin="anonymous"></script>
            <script src="https://cdn.jsdelivr.net/npm/typed.js@2.0.12"></script>
            <script src="app/dashboard.js"></script>
        ';

        $data['subscription'] = $this->subscriptionModel->getUserSubscription(hashidsDecrypt(session()->get('userID')));
        
        $counterMessages = [
            'all' => 0,
            'reply_by_manual' => 0,
            'replay_by_ai' => 0,
        ];

        $userSocials = $this->userSocialModel->getUserSocialByUserID(hashidsDecrypt(session()->get('userID')));
        foreach ($userSocials as $userSocial) {
            
            $messageRooms = $this->messageRoomModel->getMessageRoomByUserID(hashidsDecrypt(session()->get('userID')));

            foreach ($messageRooms as $room) {
                $messages = $this->messageModel->getMessageRoomByRoomID($room->id, 'ALL');
                $messagesManul = $this->messageModel->getMessageRoomByRoomID($room->id, 'MANUL');
                $messagesAI = $this->messageModel->getMessageRoomByRoomID($room->id, 'AI');
                $counterMessages['all'] += count($messages);
                $counterMessages['reply_by_manual'] += count($messagesManul);
                $counterMessages['replay_by_ai'] += count($messagesAI);
            }   
            
            $userSocial->id = hashidsEncrypt($userSocial->id);
        }

        $data['userSocials'] = $userSocials;
        $data['counterMessages'] = $counterMessages;
        $data['teams'] = $this->teamModel->getTeamByOwnerID(hashidsDecrypt(session()->get('userID')));

        echo view('/app', $data);
    }

    public function policy()
    {
        echo view('/policy');
    }
}
