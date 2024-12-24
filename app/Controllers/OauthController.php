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

    public function _callback()
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

        $this->userModel->updateUserByID(session()->get('userID'), [
            'access_token_meta' => $accessToken
        ]);

        return <<<HTML
        <script>
            window.opener.postMessage({ success: true, token: "{$accessToken}" }, "*");
            window.close();
        </script>
HTML;
    }

    public function callback()
    {
        $platform = $this->request->getGet('platform'); // ตรวจสอบแพลตฟอร์มจาก query parameter
        $code = $this->request->getGet('code');

        // if (!isset($code)) {
        //     die('Authorization code not found.');
        // }

        switch ($platform) {
            case 'Facebook':
                $this->handleFacebookCallback($code);
                break;
            case 'Instagram':
                $this->handleInstagramCallback($code);
                break;
            case 'WhatsApp':
                $this->handleWhatsAppCallback($code);
                break;
            default:
                // return $this->respond(['message' => 'Unknown platform'], 400);
        }

        return <<<HTML
        <script>
            window.opener.postMessage({ success: true }, "*");
            window.close();
        </script>
HTML;
    }

    public function checkToken($platform)
    {

        $status = 500;

        switch ($platform) {
            case 'facebook':
                $user = $this->userModel->getUserByID(session()->get('userID'));

                if ($user->access_token_meta == '') {
                    $status = 200;
                    $response['data'] = 'NO TOKEN';
                }

                break;
        }


        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setJSON($response);
    }

    public function policy()
    {
        echo view('/policy');
    }

    private function handleFacebookCallback($code)
    {
        $client = new Client();
        $clientId = getenv('APP_ID');
        $clientSecret = getenv('APP_SECRET');
        $redirectUri = base_url('/callback?platform=Facebook');

        $authCode = $code;

        $response = $client->post('https://graph.facebook.com/v21.0/oauth/access_token', [
            'form_params' => [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'code' => $authCode,
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        $accessToken = $data['access_token'];

        $this->userModel->updateUserByID(session()->get('userID'), [
            'access_token_meta' => $accessToken
        ]);
    }

    private function handleInstagramCallback($code)
    {

        echo '1'; exit();
        $client = new Client();
        $clientId = getenv('APP_ID');
        $clientSecret = getenv('APP_SECRET');
        $redirectUri = base_url('/callback');

        $authCode = $code;

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

        $this->userModel->updateUserByID(session()->get('userID'), [
            'access_token_meta' => $accessToken
        ]);
    }

    private function handleWhatsAppCallback($code)
    {
        // โค้ดสำหรับ WhatsApp OAuth
    }
}
