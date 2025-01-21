<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\SubscriptionModel;

class ProfileController extends BaseController
{
    private SubscriptionModel $subscriptionModel;
    private UserModel $userModel;

    public function __construct()
    {
        $this->subscriptionModel = new SubscriptionModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $data['content'] = 'profile/index';
        $data['title'] = 'Profile';
        $data['css_critical'] = '';
        $data['js_critical'] = ' 
            <script src="https://code.jquery.com/jquery-3.7.1.js" crossorigin="anonymous"></script>
            <script src="app/profile.js"></script>
        ';

        $data['subscription'] = $this->subscriptionModel->getUserSubscription(hashidsDecrypt(session()->get('userID')));

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
}
