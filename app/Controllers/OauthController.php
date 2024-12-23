<?php

namespace App\Controllers;

use GuzzleHttp\Client;

use App\Factories\HandlerFactory;
use App\Models\CustomerModel;
use App\Models\MessageModel;
use App\Models\MessageRoomModel;
use App\Models\UserModel;
use App\Models\UserSocialModel;
use App\Services\MessageService;
use CodeIgniter\HTTP\ResponseInterface;


class OauthController extends BaseController
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

    public function callback()
    {
        $client = new Client();
        $clientId = getenv('APP_ID');
        $clientSecret = getenv('APP_SECRET');
        $redirectUri = base_url('/callback');
        
        if (!isset($_GET['code'])) {
            die('Authorization code not found.');
        }
        
        $authCode = $_GET['code'];
        
        $response = $client->post('https://graph.facebook.com/v21.0/oauth/access_token', [
            'form_params' => [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'code' => $authCode,
            ],
        ]);
        
        // TODO:: HANDLE REFACTOR
        $data = json_decode($response->getBody(), true);
        $accessToken = $data['access_token'];

        $this->userModel->updateUserByID(1, [
            'access_token_meta' => $accessToken
        ]);

        echo "Access Token: $accessToken";
    }

    public function policy()
    {
        echo view('/policy');
    }
}
