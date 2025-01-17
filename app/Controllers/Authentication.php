<?php

namespace App\Controllers;

use App\Integrations\Facebook\FacebookClient;
use App\Models\TeamModel;
use App\Models\TeamMemberModel;
use App\Models\UserModel;
use App\Models\SubscriptionModel;

class Authentication extends BaseController
{

    private $config;

    private UserModel $userModel;
    private SubscriptionModel $subscriptionModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->subscriptionModel = new SubscriptionModel();
    }

    public function index()
    {
        $data['title'] = 'Signup';

        echo view('/auth/signup');
    }

    public function password()
    {
        $email = $this->request->getGet('email');

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
            $userOwnerID = $requestPayload->user_owner_id ? hashidsDecrypt($requestPayload->user_owner_id) : null;

            if (!$username || !$password) throw new \Exception('กรุณาตรวจสอบ username หรือ password ของท่าน');

            $users = $this->userModel->getUser($username);

            if ($users) {
                throw new \Exception('มียูสนี้แล้ว');
            } else {

                $userID = $this->userModel->insertUser([
                    'login_type' => 'default',
                    'username' => $username,
                    'email' => $username,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'user_owner_id' => $userOwnerID,
                    'picture' => $userOwnerID ? getAvatar() : ''
                ]);

                $user = $this->userModel->getUserByID($userID);

                $userSubscription = $this->subscriptionModel->getUserSubscription($user->id);

                session()->set([
                    'userID' => hashidsEncrypt($user->id),
                    'login_type' => $user->login_type,
                    'thumbnail' => $user->picture,
                    'isUserLoggedIn' => true,
                    'subscription_status' => $userSubscription ? $userSubscription->status : '',
                    'subscription_current_period_start' => $userSubscription ? $userSubscription->current_period_start : '',
                    'subscription_current_period_end' => $userSubscription ? $userSubscription->current_period_end : '',
                    'subscription_cancel_at_period_end' => $userSubscription ? $userSubscription->cancel_at_period_end : '',
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
            $this->subscriptionModel = new \App\Models\SubscriptionModel();

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

                            $userSubscription = $this->subscriptionModel->getUserSubscription($user->id);

                            session()->set([
                                'userID' => hashidsEncrypt($user->id),
                                // 'username' => $user->username,
                                'name' => $user->username,
                                'login_type' => $user->login_type,
                                'thumbnail' => $user->picture,
                                'isUserLoggedIn' => true,
                                'subscription_status' => $userSubscription ? $userSubscription->status : '',
                                'subscription_current_period_start' => $userSubscription ? $userSubscription->current_period_start : '',
                                'subscription_current_period_end' => $userSubscription ? $userSubscription->current_period_end : '',
                                'subscription_cancel_at_period_end' => $userSubscription ? $userSubscription->cancel_at_period_end : '',
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

                $redirectUri = base_url('auth/callback/facebook');

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

    public function authCallback($platform)
    {

        switch ($platform) {

            case 'facebook':

                $state = session()->get('oauth_state');
                if (!$state || $state !== $this->request->getGet('state')) return $this->response->setJSON(['error' => 'Invalid state parameter.']);

                $code = $this->request->getGet('code');
                if (!$code) return $this->response->setJSON(['error' => 'Authorization code not found.']);

                $redirectUri = base_url('auth/callback/facebook');

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

                $userSubscription = $this->subscriptionModel->getUserSubscription($user->id);

                session()->set([
                    'userID' => hashidsEncrypt($user->id),
                    // 'username' => $user->username,
                    'name' => $user->name,
                    'platform' => $user->sign_by_platform,
                    'thumbnail' => $user->picture,
                    'isUserLoggedIn' => true,
                    'subscription_status' => $userSubscription ? $userSubscription->status : '',
                    'subscription_current_period_start' => $userSubscription ? $userSubscription->current_period_start : '',
                    'subscription_current_period_end' => $userSubscription ? $userSubscription->current_period_end : '',
                    'subscription_cancel_at_period_end' => $userSubscription ? $userSubscription->cancel_at_period_end : '',
                ]);

                break;
        }

        return redirect()->to('/');
    }
}
