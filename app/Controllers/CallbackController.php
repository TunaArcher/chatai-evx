<?php

namespace App\Controllers;

use App\Integrations\Facebook\FacebookClient;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\CustomerModel;
use App\Models\MessageModel;
use App\Models\MessageRoomModel;
use App\Models\UserModel;

class CallbackController extends BaseController
{
    private $config;

    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();

        $this->config = [
            'facebook' => [
                'client_id' => '2356202511392731',
                'client_secret' => '63c8560ce123d8112357dd7e6d8fcab4',
                'redirect_uri' => base_url('callback/facebook'),
                'token_url' => 'https://graph.facebook.com/v21.0/oauth/access_token',
            ],
            'instagram' => [
                'token_url' => 'https://api.instagram.com/oauth/access_token',
            ],
            'whatsapp' => [
                'token_url' => 'https://graph.facebook.com/v21.0/oauth/access_token',
            ],
        ];
    }

    public function handle($platform)
    {
        switch ($platform) {

            case 'facebook':

                $state = session()->get('oauth_state');
                if (!$state || $state !== $this->request->getGet('state')) return $this->response->setJSON(['error' => 'Invalid state parameter.']);

                $code = $this->request->getGet('code');
                if (!$code) return $this->response->setJSON(['error' => 'Authorization code not found.']);

                $platformConfig = $this->config[$platform];

                $response = $this->fetchAccessToken($platformConfig['token_url'], $platform, $code);

                $faceBookAPI = new FacebookClient([
                    'accessToken' => $response->access_token,
                ]);

                $profile = $faceBookAPI->getProfile();

                $user = $this->userModel->getUserByPlatFromAndID($platform, $profile->id);

                if (!$user) {

                    $userID = $this->userModel->insertUser([
                        'sign_by_platform' => $platform,
                        'platform_user_id' => $profile->id,
                        'name' => $profile->name,
                        'picture' => $profile->picture->data->url,
                        'access_token_meta' => $response->access_token
                    ]);

                    $user = $this->userModel->getUserByID($userID);
                }

                session()->set([
                    'userID' => $user->id,
                    // 'username' => $user->username,
                    'name' => $user->name,
                    'platform' => $user->sign_by_platform,
                    'thumbnail' => $user->picture,
                    'isUserLoggedIn' => true
                ]);

                break;
        }

        return redirect()->to('/');
    }

    private function fetchAccessToken($tokenUrl, $platform, $code)
    {
        $client = \Config\Services::curlrequest();
        $response = $client->post($tokenUrl, [
            'form_params' => [
                'client_id' => $this->config[$platform]['client_id'],
                'client_secret' => $this->config[$platform]['client_secret'],
                'redirect_uri' => $this->config[$platform]['redirect_uri'],
                'code' => $code,
            ],
        ]);

        return json_decode($response->getBody());
    }
}
