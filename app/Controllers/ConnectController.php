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


class ConnectController extends BaseController
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

    public function connectPageToApp()
    {
        $userID = session()->get('userID');

        $user = $this->userModel->getUserByID($userID);

        $input = $this->request->getJSON();

        $pageID = $input->pageID;
        $pageToken = '';

        $faceBookAPI = new FacebookClient([
            'accessToken' => $user->access_token_meta
        ]);
        $getFbPagesList = $faceBookAPI->getFbPagesList();
        foreach ($getFbPagesList->data as $page) {

            if ($page->id == $pageID) {
                $pageName = $page->name;
                $pageToken = $page->access_token;
                break;
            }
        }

        $subscribedApps = $faceBookAPI->subscribedApps($pageID, $pageToken);

        if ($subscribedApps) {

            $this->userSocialModel->insertUserSocial([
                'user_id' => $userID,
                'platform' => 'Facebook',
                'name' => $pageName,
                'fb_token' => $pageToken,
                'is_connect' => '1',
                'page_id' => $pageID
            ]);

            $status = 200;
            $response = [
                'success' => 1,
                'message' => '',
            ];
        }

        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setJSON($response);
    }
}
