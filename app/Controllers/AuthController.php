<?php

namespace App\Controllers;

use GuzzleHttp\Client;

use App\Factories\HandlerFactory;
use App\Integrations\Facebook\FacebookClient;
use App\Models\CustomerModel;
use App\Models\MessageModel;
use App\Models\MessageRoomModel;
use App\Models\UserModel;
use App\Models\UserSocialModel;
use App\Services\MessageService;
use CodeIgniter\HTTP\ResponseInterface;


class AuthController extends BaseController
{

    private MessageService $messageService;
    private CustomerModel $customerModel;
    private MessageModel $messageModel;
    private MessageRoomModel $messageRoomModel;
    private UserModel $userModel;
    private UserSocialModel $userSocialModel;

    public function __construct()
    {
        $this->messageService = new MessageService();
        $this->customerModel = new CustomerModel();
        $this->messageModel = new MessageModel();
        $this->messageRoomModel = new MessageRoomModel();
        $this->userModel = new UserModel();
        $this->userSocialModel = new UserSocialModel();
    }

    public function FbPagesList()
    {
        $userID = session()->get('userID');

        $user = $this->userModel->getUserByID($userID);

        $faceBookAPI = new FacebookClient([
            'accessToken' => $user->access_token_meta
        ]);

        $getFbPagesList = $faceBookAPI->getFbPagesList();

        $data = [
            'type' => 'list',
            'data' => [
                'pages' => []
            ]
        ];

        foreach ($getFbPagesList->data as $page) {
            $data['data']['pages'][] = [
                'id' => $page->id ?? '',
                'name' => $page->name ?? '',
                'status' => $this->userSocialModel->getUserSocialByPageID($page->id) ? 'connected' : 'not_connected',
                'identifier' => 'fb',
                'ava' => $faceBookAPI->getPicturePage($page->id),
                'account_owner' => null,
            ];
        }

        $status = 200;
        $response = $data;

        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setJSON($response);
    }
}
