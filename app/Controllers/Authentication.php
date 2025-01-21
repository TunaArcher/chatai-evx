<?php

namespace App\Controllers;

use App\Integrations\Facebook\FacebookClient;
use App\Models\UserModel;
use App\Models\UserAccountModel;
use App\Models\SubscriptionModel;

class Authentication extends BaseController
{

    private $config;

    private UserModel $userModel;
    private UserAccountModel $userAccountModel;
    private SubscriptionModel $subscriptionModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userAccountModel = new UserAccountModel();
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
            $email = $requestPayload->email ?? null;
            $password = $requestPayload->password ?? null;
            $userOwnerID = isset($requestPayload->user_owner_id) ? hashidsDecrypt($requestPayload->user_owner_id) : null;

            if (!$email || !$password) throw new \Exception('กรุณาตรวจสอบ email หรือ password ของท่าน');

            $users = $this->userModel->getUser($email);
            if ($users) throw new \Exception('มียูสนี้แล้ว');

            $userID = $this->userModel->insertUser([
                'main_sign_in_by' => 'default',
                'email' => $email,
                'name' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'user_owner_id' => $userOwnerID,
                'picture' => $userOwnerID ? getAvatar() : ''
            ]);

            $user = $this->userModel->getUserByID($userID);

            $userSubscription = $this->subscriptionModel->getUserSubscription($user->id);

            session()->set([
                'userID' => hashidsEncrypt($user->id),
                'main_sign_in_by' => $user->main_sign_in_by,
                'email' => $user->email,
                'name' => $user->name,
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
                                'main_sign_in_by' => $user->main_sign_in_by,
                                'email' => $user->email,
                                'name' => $user->name,
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

                $state = bin2hex(random_bytes(16));
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
                if (!$code) {
                    // return $this->response->setJSON(['error' => 'Authorization code not found.']);
                    return redirect()->to('/login');
                }

                $redirectUri = base_url('auth/callback/facebook');

                $faceBookAPI = new FacebookClient([
                    'clientID' => getenv('APP_ID'),
                    'clientSecret' => getenv('APP_SECRET'),
                ]);

                $oauthAccessToken = $faceBookAPI->oauthAccessToken($redirectUri, $code);

                $faceBookAPI->setAccessToken($oauthAccessToken->access_token);

                $profile = $faceBookAPI->getProfile();

                // หาว่าเคสสมัครหรือยัง
                $userAccount = $this->userAccountModel->getUserAccountByProviderAndProviderUserID($platform, $profile->id);

                // หากยังไม่เคยสมัคร ให้สร้างยูสใหม่
                if (!$userAccount) {

                    $userID = $this->userModel->insertUser([
                        'main_sign_in_by' => $platform,
                        'email' => $profile->email ?? '',
                        'name' => $profile->name,
                        'picture' => $profile->picture->data->url,
                        'meta_access_token' => $oauthAccessToken->access_token,
                    ]);

                    $userAccount = $this->userAccountModel->insertUserAccount([
                        'user_id' => $userID,
                        'email' => $profile->email ?? '',
                        'provider' => $platform,
                        'provider_user_id' => $profile->id,
                        'access_token' => $oauthAccessToken->access_token,
                        // 'refresh_token ' => '',
                        // 'expires_at' => '',
                        'linked_at' => date('Y-m-d H:i:s'),
                        'picture' => $profile->picture->data->url,
                    ]);

                    $user = $this->userModel->getUserByID($userID);
                }

                // หากเคยสมัครแล้ว อัพเดทข้อมูล
                else {

                    $user = $this->userModel->getUserByID($userAccount->user_id);
                    $userID = $user->id;

                    $this->userModel->updateUserByID($user->id, [
                        'name' => $profile->name,
                        'email' => $profile->email ?? '',
                        'picture' => $profile->picture->data->url,
                        'meta_access_token' => $oauthAccessToken->access_token,
                    ]);

                    $this->userAccountModel->updateUserAccountByID($userAccount->id, [
                        'email' => $profile->email ?? '',
                        'access_token' => $oauthAccessToken->access_token,
                        'picture' => $profile->picture->data->url,
                    ]);
                }

                $userSubscription = $this->subscriptionModel->getUserSubscription($user->id);

                session()->set([
                    'userID' => hashidsEncrypt($user->id),
                    'main_sign_in_by' => $user->main_sign_in_by,
                    'email' => $user->email,
                    'name' => $user->name,
                    'platform' => $user->main_sign_in_by,
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
