<?php

namespace App\Controllers;

use GuzzleHttp\Client;

use App\Integrations\Facebook\FacebookClient;
use App\Integrations\Instagram\InstagramClient;
use App\Integrations\WhatsApp\WhatsAppClient;
use App\Models\UserModel;
use App\Models\UserSocialModel;

class ConnectController extends BaseController
{
    private UserModel $userModel;
    private UserSocialModel $userSocialModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userSocialModel = new UserSocialModel();
    }

    public function connectToApp()
    {
        $userID = hashidsDecrypt(session()->get('userID'));

        $user = $this->userModel->getUserByID($userID);

        $input = $this->request->getJSON();

        switch ($input->platform) {

            case 'Facebook':

                $pageID = $input->pageID;
                $src = $input->src;
                $pageToken = '';

                $faceBookAPI = new FacebookClient([
                    'accessToken' => $user->meta_access_token
                ]);
                $getFbPagesList = $faceBookAPI->getFbPagesList();
                foreach ($getFbPagesList->data as $page) {

                    if ($page->id == $pageID) {
                        $pageName = $page->name;
                        $pageToken = $page->access_token;
                        break;
                    }
                }

                $subscribedApps = $faceBookAPI->subscribedApps($pageID, $pageToken);

                if ($subscribedApps) {

                    $this->userSocialModel->insertUserSocial([
                        'user_id' => $userID,
                        'platform' => 'Facebook',
                        'name' => $pageName,
                        'fb_token' => $pageToken,
                        'is_connect' => '1',
                        'page_id' => $pageID,
                        'src' => $src
                    ]);

                    $status = 200;
                    $response = [
                        'success' => 1,
                        'message' => '',
                    ];
                }

                break;

            case 'WhatsApp':

                $WABID = $input->pageID;
                $name = $input->pageName;

                $whatsAppAPI = new WhatsAppClient([
                    'whatsAppToken' => $user->whatsapp_access_token
                ]);

                $whatsappPhoneNumberID = $whatsAppAPI->getPhoneNumberId($WABID);

                $subscribedApps = $whatsAppAPI->subscribedApps($WABID);

                if ($subscribedApps) {

                    $this->userSocialModel->insertUserSocial([
                        'user_id' => $userID,
                        'platform' => 'WhatsApp',
                        'name' => $name,
                        'is_connect' => '1',
                        'page_id' => $WABID,
                        'whatsapp_phone_number_id' => $whatsappPhoneNumberID,
                    ]);

                    $status = 200;
                    $response = [
                        'success' => 1,
                        'message' => '',
                    ];
                }

                break;

            case 'Instagram':
                
                $instagramBusinessAccountID = $input->pageID;
                $name = $input->pageName;

                $instagramAPI = new InstagramClient([
                    'accessToken' => $user->instagram_access_token
                ]);

                $subscribedApps = $instagramAPI->subscribedApps($instagramBusinessAccountID);

                if ($subscribedApps) {

                    $this->userSocialModel->insertUserSocial([
                        'user_id' => $userID,
                        'platform' => 'Instagram',
                        'name' => $name,
                        'is_connect' => '1',
                        'page_id' => $instagramBusinessAccountID
                    ]);

                    $status = 200;
                    $response = [
                        'success' => 1,
                        'message' => '',
                    ];
                }

                break;
        }

        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setJSON($response);
    }
}
