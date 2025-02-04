<?php

namespace App\Controllers;

use App\Integrations\Facebook\FacebookClient;
use App\Models\UserModel;
use App\Models\UserAccountModel;
use App\Models\SubscriptionModel;
use Google_Client;

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

            if ($userOwnerID) {

                $user = $this->userModel->getUserByEmail($email);

                if ($user->accept_invite == 'done') throw new \Exception('มียูสนี้แล้ว');

                else if ($user->accept_invite == 'waiting') {
                    $this->userModel->updateUserByID($user->id, [
                        'accept_invite' => 'done',
                        'main_sign_in_by' => 'default',
                        'name' => $email,
                        'password' => password_hash($password, PASSWORD_DEFAULT),
                        'permission_ids' => '1,2,3,5'
                    ]);
                }

                $userID = $user->id;
            } else {

                $users = $this->userModel->getUser($email);

                if ($users) throw new \Exception('มียูสนี้แล้ว');

                $userID = $this->userModel->insertUser([
                    'accept_invite' => $userOwnerID ? 'done' : '',
                    'main_sign_in_by' => 'default',
                    'email' => $email,
                    'name' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'user_owner_id' => $userOwnerID,
                    'permission_ids' => '1,2,3,4,5,6'
                ]);
            }

            $user = $this->userModel->getUserByID($userID);

            $userSubscription = $this->subscriptionModel->getUserSubscription($user->id);

            session()->set([
                'user_owner_id' => hashidsEncrypt($user->user_owner_id),
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
                'permissions' => $user->permission_ids,
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
                                'user_owner_id' => hashidsEncrypt($user->user_owner_id),
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
                                'permissions' => $user->permission_ids,
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
            'google' => []
        ];

        if (!isset($this->config[$platform])) return redirect()->to('/')->with('error', 'Invalid platform selected.');

        switch ($platform) {

            case 'facebook':

                // สร้าง state เพื่อป้องกัน CSRF
                $state = bin2hex(random_bytes(16));
                session()->set('oauth_state', $state);
                session()->set('platform', $platform);

                $redirectUri = base_url('auth/callback/facebook');

                $authUrl = 'https://facebook.com/v21.0/dialog/oauth' . '?' . http_build_query([
                    'client_id' => getenv('APP_ID'),
                    'redirect_uri' => $redirectUri,
                    'scope' => 'email,public_profile,pages_manage_metadata',
                    'response_type' => 'code',
                    'state' => $state,
                ]);

                break;

            case 'google':

                // สร้าง state เพื่อป้องกัน CSRF
                $state = bin2hex(random_bytes(16));
                session()->set('oauth_state', $state);
                session()->set('platform', $platform);

                // URL สำหรับ Redirect กลับหลังจากล็อกอินสำเร็จ
                $redirectUri = base_url('auth/callback/google');

                // สร้าง URL สำหรับ Google OAuth
                $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth' . '?' . http_build_query([
                    'client_id' => getenv('GOOGLE_CLIENT_ID'), // Client ID ของ Google
                    'redirect_uri' => $redirectUri, // URL สำหรับ Callback
                    'scope' => 'email profile', // ขอสิทธิ์การเข้าถึง email และข้อมูลโปรไฟล์
                    'response_type' => 'code', // ขอ Authorization Code
                    'state' => $state, // ใช้สำหรับป้องกัน CSRF
                    'access_type' => 'offline', // ขอ Refresh Token
                    'prompt' => 'consent' // บังคับให้แสดงหน้าขอสิทธิ์ทุกครั้ง
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
                        'permission_ids' => '1,2,3,4,5,6'
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
                    'user_owner_id' => hashidsEncrypt($user->user_owner_id),
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
                    'permissions' => $user->permission_ids,
                ]);

                break;

            case 'google':

                // ตรวจสอบ state เพื่อป้องกัน CSRF
                $state = session()->get('oauth_state');
                if (!$state || $state !== $this->request->getGet('state')) {
                    return $this->response->setJSON(['error' => 'Invalid state parameter.']);
                }

                // รับ Authorization Code จาก URL
                $code = $this->request->getGet('code');
                if (!$code) {
                    return redirect()->to('/login');
                }

                $redirectUri = base_url('auth/callback/google');

                // สร้าง Google API Client
                $googleClient = new Google_Client();
                $googleClient->setClientId(getenv('GOOGLE_CLIENT_ID'));
                $googleClient->setClientSecret(getenv('GOOGLE_CLIENT_SECRET'));
                $googleClient->setRedirectUri($redirectUri);

                // แลกรหัส Authorization Code กับ Access Token
                try {
                    $token = $googleClient->fetchAccessTokenWithAuthCode($code);
                } catch (\Exception $e) {
                    return $this->response->setJSON(['error' => 'Failed to fetch access token: ' . $e->getMessage()]);
                }

                if (isset($token['error'])) {
                    return $this->response->setJSON(['error' => $token['error']]);
                }

                $googleClient->setAccessToken($token['access_token']);

                // ดึงข้อมูลโปรไฟล์ผู้ใช้จาก Google
                $googleService = new \Google_Service_Oauth2($googleClient);
                $profile = $googleService->userinfo->get();

                // หาว่าเคสสมัครหรือยัง
                $userAccount = $this->userAccountModel->getUserAccountByProviderAndProviderUserID($platform, $profile->id);

                // หากยังไม่เคยสมัคร ให้สร้างยูสใหม่
                if (!$userAccount) {
                    $userID = $this->userModel->insertUser([
                        'main_sign_in_by' => $platform,
                        'email' => $profile->email ?? '',
                        'name' => $profile->name,
                        'picture' => $profile->picture ?? '',
                        'permission_ids' => '1,2,3,4,5,6'
                    ]);

                    $userAccount = $this->userAccountModel->insertUserAccount([
                        'user_id' => $userID,
                        'email' => $profile->email ?? '',
                        'provider' => $platform,
                        'provider_user_id' => $profile->id,
                        'access_token' => $token['access_token'],
                        'refresh_token' => $token['refresh_token'] ?? '',
                        'expires_at' => date('Y-m-d H:i:s', time() + $token['expires_in']),
                        'linked_at' => date('Y-m-d H:i:s'),
                        'picture' => $profile->picture ?? '',
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
                        'picture' => $profile->picture ?? '',
                    ]);

                    $this->userAccountModel->updateUserAccountByID($userAccount->id, [
                        'email' => $profile->email ?? '',
                        'access_token' => $token['access_token'],
                        'refresh_token' => $token['refresh_token'] ?? '',
                        'expires_at' => date('Y-m-d H:i:s', time() + $token['expires_in']),
                        'picture' => $profile->picture ?? '',
                    ]);
                }

                // ดึงข้อมูล Subscription ของผู้ใช้
                $userSubscription = $this->subscriptionModel->getUserSubscription($user->id);

                // ตั้งค่า Session
                session()->set([
                    'user_owner_id' => hashidsEncrypt($user->user_owner_id),
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
                    'permissions' => $user->permission_ids,
                ]);

                break;
        }

        return redirect()->to('/');
    }
}
