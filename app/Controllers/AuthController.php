<?php

namespace App\Controllers;

use GuzzleHttp\Client;

use App\Integrations\Facebook\FacebookClient;
use App\Integrations\Instagram\InstagramClient;
use App\Integrations\WhatsApp\WhatsAppClient;
use App\Models\UserModel;
use App\Models\UserSocialModel;

class AuthController extends BaseController
{
    private UserModel $userModel;
    private UserSocialModel $userSocialModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userSocialModel = new UserSocialModel();
    }

    public function checkToken($platform)
    {

        $status = 500;
        $response['data'] = '';

        $user = $this->userModel->getUserByID(hashidsDecrypt(session()->get('userID')));

        switch ($platform) {
            case 'Facebook':
                if ($user->meta_access_token == '') $response['data'] = 'NO TOKEN';
                $status = 200;
                break;

            case 'Instagram':
                if ($user->instagram_access_token == '') $response['data'] = 'NO TOKEN';
                $status = 200;
                break;

            case 'WhatsApp':
                if ($user->whatsapp_access_token == '') $response['data'] = 'NO TOKEN';
                break;
        }

        $status = 200;

        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setJSON($response);
    }

    public function FbPagesList()
    {

        $user = $this->userModel->getUserByID(hashidsDecrypt(session()->get('userID')));

        $faceBookAPI = new FacebookClient(['accessToken' => $user->meta_access_token]);

        $getFbPagesList = $faceBookAPI->getFbPagesList();

        $data = [
            'type' => 'list',
            'data' => [
                'pages' => []
            ]
        ];

        foreach ($getFbPagesList->data as $page) {

            $userSocial = $this->userSocialModel->getUserSocialByPageID('Facebook', $page->id);

            $data['data']['pages'][] = [
                'id' => $page->id ?? '',
                'name' => $page->name ?? '',
                'status' => $userSocial && $userSocial->is_connect ? 'connected' : 'not_connected',
                'identifier' => 'fb',
                'ava' => $faceBookAPI->getPicturePage($page->id),
                'account_owner' => null,
            ];
        }

        $status = 200;
        $response = $data;

        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setJSON($response);
    }

    public function WABListBusinessAccounts()
    {
        $userID = hashidsDecrypt(session()->get('userID'));

        $user = $this->userModel->getUserByID($userID);

        $whatsAppAPI = new WhatsAppClient([
            'whatsAppToken' => $user->whatsapp_access_token
        ]);

        $businesses = $whatsAppAPI->getBussinessID();

        $businessId = $businesses->data ?? false; // เลือก Business ID ตัวแรก
        if (!$businessId) {
            throw new \Exception('No Business ID associated with this account.');
        }

        $data = [
            'type' => 'list',
            'data' => [
                'pages' => []
            ]
        ];

        foreach ($businesses->data as $businessesData) {

            // $bundle['id']
            // $data->id;
            // $data->name;

            $businessId = $businessesData->id;

            $getListBusinessAccounts = $whatsAppAPI->getListBusinessAccounts($businessId);

            if ($getListBusinessAccounts) {

                foreach ($getListBusinessAccounts->data as $account) {

                    $userSocial = $this->userSocialModel->getUserSocialByPageID('WhatsApp', $account->id);

                    $wabID = $account->id;
                    $wabName = '';

                    if ($userSocial) {
                        $wabName = $userSocial->name;

                        $data['data']['pages'][] = [
                            'id' => $wabID,
                            'name' => $userSocial->name,
                            'status' => $userSocial && $userSocial->is_connect ? 'connected' : 'not_connected',
                            // 'identifier' => 'fb',
                            'ava' => '',
                            // 'account_owner' => null,
                        ];
                    } else {
                        // $phoneNumber = $whatsAppAPI->getPhoneNumber($wabID);
                        $wabName = $account->name;

                        $data['data']['pages'][] = [
                            'id' => $wabID,
                            'name' => $businessesData->name . ' | ' . $wabName,
                            'status' => $userSocial && $userSocial->is_connect ? 'connected' : 'not_connected',
                            // 'identifier' => 'fb',
                            'ava' => '',
                            // 'account_owner' => null,
                        ];
                    }
                }
            }
        }

        $status = 200;
        $response = $data;

        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setJSON($response);
    }

    // public function IGListBusinessAccounts()
    // {
    //     $userID = hashidsDecrypt(session()->get('userID'));

    //     $user = $this->userModel->getUserByID($userID);

    //     $instagramAPI = new InstagramClient([
    //         'accessToken' => $user->access_token_instagram
    //     ]);

    //     $getListBusinessAccounts = $instagramAPI->getListBusinessAccounts();

    //     echo '<pre>';
    //     print_r($getListBusinessAccounts); exit();

    //     $data = [];
    //     foreach ($getListBusinessAccounts->data as $account) {

    //         if (property_exists($account, 'instagram_business_account')) {

    //             $accountIG = $account->instagram_business_account;
    //             $userSocial = $this->userSocialModel->getUserSocialByPageID('Instagram', $accountIG->id);

    //             $accountIGProfile = $instagramAPI->getUserProfile($accountIG->id);

    //             if ($userSocial) {

    //                 $data['data']['pages'][] = [
    //                     'id' => $accountIGProfile->id,
    //                     'name' => $userSocial->name,
    //                     'status' => $userSocial && $userSocial->is_connect ? 'connected' : 'not_connected',
    //                     'ava' => $accountIGProfile->profile_picture_url,
    //                 ];
    //             } else {

    //                 $data['data']['pages'][] = [
    //                     'id' => $accountIGProfile->id,
    //                     'name' => $accountIGProfile->name,
    //                     'status' => $userSocial && $userSocial->is_connect ? 'connected' : 'not_connected',
    //                     'ava' => $accountIGProfile->profile_picture_url,
    //                 ];
    //             }
    //         }
    //     }

    //     $status = 200;
    //     $response = $data;

    //     return $this->response
    //         ->setStatusCode($status)
    //         ->setContentType('application/json')
    //         ->setJSON($response);
    // }

    private function mockup()
    {
        return json_decode(
            '{
  "data": [
    {
      "id": "1234567890",
      "name": "Account 1",
      "account_status": "ACTIVE"
    },
    {
      "id": "9876543210",
      "name": "Account 2",
      "account_status": "ACTIVE"
    }
  ]
}'
        );
    }
}
