<?php

namespace App\Controllers;

use App\Integrations\Facebook\FacebookClient;
use App\Models\UserModel;

class Authentication extends BaseController
{

    private $config;

    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $data['title'] = 'Signup';

        echo view('/auth/signup');
    }

    public function password()
    {
        $email = $this->request->getGet('email'); // ชื่อพารามิเตอร์คือ 'email'

        $data['title'] = 'Signup';
        $data['email'] = $email;

        echo view('/auth/password', $data);
    }

    public function authRegister()
    {
        $data['title'] = 'Regsiter';

        echo view('/auth/register');
    }

    public function register()
    {
        $status = 500;
        $response['success'] = 0;
        $response['message'] = '';

        try {

            if ($this->request->getMethod() != 'post') throw new \Exception('Invalid Credentials.');

            $requestPayload = $this->request->getJSON();
            $username = $requestPayload->username ?? null;
            $email = $requestPayload->email ?? null;
            $password = $requestPayload->password ?? null;

            if (!$username || !$password) throw new \Exception('กรุณาตรวจสอบ username หรือ password ของท่าน');

            $users = $this->userModel->getUser($username);

            if ($users) {
                throw new \Exception('มียูสนี้แล้ว');
            } else {

                $userID = $this->userModel->insertUser([
                    'username' => $email,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT)
                ]);

                $user = $this->userModel->getUserByID($userID);

                session()->set([
                    'userID' => $user->id,
                    // 'username' => $user->username,
                    'name' => $user->name,
                    'platform' => $user->sign_by_platform,
                    'thumbnail' => $user->picture,
                    'isUserLoggedIn' => true
                ]);

                $status = 200;
                $response['success'] = 1;
                $response['message'] = 'เข้าสู่ระบบสำเร็จ';

                $response['redirect_to'] = base_url('/');
            }
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setJSON($response);
    }

    public function login()
    {
        $status = 500;
        $response['success'] = 0;
        $response['message'] = '';

        try {

            if ($this->request->getMethod() != 'post') throw new \Exception('Invalid Credentials.');

            $this->userModel = new \App\Models\UserModel();

            $requestPayload = $this->request->getJSON();
            $username = $requestPayload->username ?? null;
            $password = $requestPayload->password ?? null;

            if (!$username || !$password) throw new \Exception('กรุณาตรวจสอบ username หรือ password ของท่าน');

            $users = $this->userModel->getUser($username);

            if ($users) {

                foreach ($users as $user) {

                    if ($user->login_fail < 5) {

                        if (password_verify($password, $user->password)) {

                            $this->userModel->updateUserByID($user->id, ['login_fail' => 0]);

                            session()->set([
                                'userID' => $user->id,
                                // 'username' => $user->username,
                                'name' => $user->username,
                                'platform' => $user->sign_by_platform,
                                'thumbnail' => $user->picture,
                                'isUserLoggedIn' => true
                            ]);

                            $status = 200;
                            $response['success'] = 1;
                            $response['message'] = 'เข้าสู่ระบบสำเร็จ';

                            $response['redirect_to'] = base_url('/');
                        } else {
                            $missedTotal = $user->login_fail + 1;
                            $this->userModel->updateUserByID($user->id, ['login_fail' => $missedTotal]);
                            throw new \Exception('กรุณาตรวจสอบ username หรือ password ของท่าน ' . "$missedTotal/5");
                        }
                    } else {
                        throw new \Exception('User ของท่านถูกล็อค');
                    }
                }
            } else {
                throw new \Exception('กรุณาตรวจสอบ username หรือ password ของท่าน');
            }
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setJSON($response);
    }

    public function logout()
    {
        try {

            session()->destroy();

            return redirect()->to('/login');
        } catch (\Exception $e) {
            //            echo $e->getMessage();
        }
    }

    public function loginByPlamform($platform)
    {

        $this->config = [
            'facebook' => [],
            'instagram' => [],
            'whatsapp' => [],
        ];

        if (!isset($this->config[$platform])) return redirect()->to('/')->with('error', 'Invalid platform selected.');

        switch ($platform) {

            case 'facebook':

                // TODO:: HANDLE Check ถ้ามี user ให้ login ไปเลย

                $state = bin2hex(random_bytes(16)); // Generate random state
                session()->set('oauth_state', $state);
                session()->set('platform', $platform);

                $redirectUri = base_url('callback/facebook');

                $authUrl = 'https://facebook.com/v21.0/dialog/oauth' . '?' . http_build_query([
                    'client_id' => getenv('APP_ID'),
                    'redirect_uri' => $redirectUri,
                    'scope' => 'email,public_profile,pages_manage_metadata,pages_read_engagement',
                    'response_type' => 'code',
                    'state' => $state,
                ]);

                break;
        }

        return redirect()->to($authUrl);
    }

    public function handleCallback($platform)
    {

        switch ($platform) {

            case 'facebook':

                $state = session()->get('oauth_state');
                if (!$state || $state !== $this->request->getGet('state')) return $this->response->setJSON(['error' => 'Invalid state parameter.']);

                $code = $this->request->getGet('code');
                if (!$code) return $this->response->setJSON(['error' => 'Authorization code not found.']);

                $redirectUri = base_url('callback/facebook');

                $faceBookAPI = new FacebookClient([
                    'clientID' => getenv('APP_ID'),
                    'clientSecret' => getenv('APP_SECRET'),
                ]);

                $oauthAccessToken = $faceBookAPI->oauthAccessToken($redirectUri, $code);

                $faceBookAPI->setAccessToken($oauthAccessToken->access_token);

                $profile = $faceBookAPI->getProfile();

                $user = $this->userModel->getUserByPlatFromAndID($platform, $profile->id);

                if (!$user) {

                    $userID = $this->userModel->insertUser([
                        'sign_by_platform' => $platform,
                        'platform_user_id' => $profile->id,
                        'name' => $profile->name,
                        'picture' => $profile->picture->data->url,
                        'access_token_meta' => $oauthAccessToken->access_token
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
}
