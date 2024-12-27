<?php

namespace App\Controllers;

use GuzzleHttp\Client;

use App\Integrations\Instagram\InstagramClient;
use App\Models\UserModel;
use App\Models\UserSocialModel;
use GuzzleHttp\Exception\ClientException;

class OauthController extends BaseController
{
    private UserModel $userModel;
    private UserSocialModel $userSocialModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userSocialModel = new UserSocialModel();
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
                $this->handleInstagramCallback($code);
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
        $response['data'] = '';

        switch ($platform) {
            case 'Facebook':
                $user = $this->userModel->getUserByID(session()->get('userID'));

                if ($user->access_token_meta == '') $response['data'] = 'NO TOKEN';

                $status = 200;

                break;

            case 'Instagram':
                $user = $this->userModel->getUserByID(session()->get('userID'));

                if ($user->access_token_instagram == '') $response['data'] = 'NO TOKEN';

                $status = 200;

                break;

            case 'WhatsApp':
                $user = $this->userModel->getUserByID(session()->get('userID'));

                if ($user->access_token_whatsapp == '') $response['data'] = 'NO TOKEN';

                $status = 200;

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
        try {

            $clientId = getenv('IG_APP_ID');
            $clientSecret = getenv('IG_APP_SECRET');
            $redirectUri = base_url('/callback');

            $authCode = $code;

            $instagramAPI = new InstagramClient([
                'clientID' => $clientId,
                'clientSecret' => $clientSecret,
            ]);

            $oauthAccessToken = $instagramAPI->oauthAccessToken($redirectUri, $authCode);

            $igUserID = $oauthAccessToken->user_id;
            $shortAccessToken = $oauthAccessToken->access_token;

            $longAccessToken = $instagramAPI->getLongAccessToken($shortAccessToken);

            $userID = session()->get('userID');

            $this->userModel->updateUserByID($userID, [
                'access_token_instagram' => $longAccessToken
            ]);

            $instagramAPI = new InstagramClient([
                'accessToken' => $longAccessToken
            ]);

            $userProfile = $instagramAPI->getUserProfile($igUserID);

            $subscribedApps = $instagramAPI->subscribedApps($igUserID, $longAccessToken);

            if ($subscribedApps) {
                $this->userSocialModel->insertUserSocial([
                    'user_id' => $userID,
                    'platform' => 'Instagram',
                    'name' => $userProfile->name,
                    'is_connect' => '1',
                    'page_id' => $userProfile->id,
                ]);
            }
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $errorBody = $response->getBody()->getContents();
            log_message('error', 'Instagram OAuth error: ' . $errorBody);
            throw new \Exception('Instagram authentication failed. Please try again.');
        }
    }

    private function handleWhatsAppCallback($code)
    {
        $client = new Client();
        $clientId = getenv('APP_ID');
        $clientSecret = getenv('APP_SECRET');
        $redirectUri = base_url('/callback?platform=WhatsApp');

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
            'access_token_whatsapp' => $accessToken
        ]);
    }
}
