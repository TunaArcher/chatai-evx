<?php

namespace App\Controllers;

use App\Models\EmployeeSettingStatusModel;

class Authentication extends BaseController
{

    private $config;

    public function __construct()
    {
        $this->config = [
            'facebook' => [
                'client_id' => '2356202511392731',
                'client_secret' => '63c8560ce123d8112357dd7e6d8fcab4',
                'redirect_uri' => base_url('callback/facebook'),
                'auth_url' => 'https://www.facebook.com/v21.0/dialog/oauth',
                'scope' => 'email,public_profile,pages_manage_metadata,pages_read_engagement',
            ],
            'instagram' => [
                'client_id' => 'YOUR_INSTAGRAM_CLIENT_ID',
                'client_secret' => 'YOUR_INSTAGRAM_CLIENT_SECRET',
                'redirect_uri' => base_url('callback/instagram'),
                'auth_url' => 'https://api.instagram.com/oauth/authorize',
                'scope' => 'user_profile,user_media',
            ],
            'whatsapp' => [
                'client_id' => 'YOUR_WHATSAPP_CLIENT_ID',
                'client_secret' => 'YOUR_WHATSAPP_CLIENT_SECRET',
                'redirect_uri' => base_url('callback/whatsapp'),
                'auth_url' => 'https://www.facebook.com/v21.0/dialog/oauth',
                'scope' => 'whatsapp_business_messaging',
            ],
        ];
    }

    public function index()
    {
        $data['title'] = 'Login';

        echo view('/auth/login');
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

            $UserModel = new \App\Models\UserModel();

            $requestPayload = $this->request->getJSON();
            $username = $requestPayload->username ?? null;
            $email = $requestPayload->email ?? null;
            $password = $requestPayload->password ?? null;

            if (!$username || !$password) throw new \Exception('กรุณาตรวจสอบ username หรือ password ของท่าน');

            $users = $UserModel->getUser($username);

            if ($users) {
                throw new \Exception('มียูสนี้แล้ว');
            } else {
                $userID = $UserModel->insertUser([
                    'username' => $username,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT)
                ]);

                $user = $UserModel->getUserByID($userID);

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

            $UserModel = new \App\Models\UserModel();

            $requestPayload = $this->request->getJSON();
            $username = $requestPayload->username ?? null;
            $password = $requestPayload->password ?? null;

            if (!$username || !$password) throw new \Exception('กรุณาตรวจสอบ username หรือ password ของท่าน');

            $users = $UserModel->getUser($username);

            if ($users) {

                foreach ($users as $user) {

                    if ($user->login_fail < 5) {

                        if (password_verify($password, $user->password)) {

                            $UserModel->updateUserByID($user->id, ['login_fail' => 0]);

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
                        } else {
                            $missedTotal = $user->login_fail + 1;
                            $UserModel->updateUserByID($user->id, ['login_fail' => $missedTotal]);
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
        if (!isset($this->config[$platform])) {
            return redirect()->to('/')->with('error', 'Invalid platform selected.');
        }

        $state = bin2hex(random_bytes(16)); // Generate random state
        session()->set('oauth_state', $state);
        session()->set('platform', $platform);

        $authUrl = $this->config[$platform]['auth_url'] . '?' . http_build_query([
            'client_id' => $this->config[$platform]['client_id'],
            'redirect_uri' => $this->config[$platform]['redirect_uri'],
            'scope' => $this->config[$platform]['scope'],
            'response_type' => 'code',
            'state' => $state,
        ]);

        return redirect()->to($authUrl);
    }
}
