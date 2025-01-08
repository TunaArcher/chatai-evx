<?php

namespace App\Controllers;

use App\Factories\HandlerFactory;
use App\Models\UserSocialModel;
use App\Services\MessageService;
use CodeIgniter\HTTP\ResponseInterface;

class WebhookController extends BaseController
{
    private MessageService $messageService;
    private UserSocialModel $userSocialModel;

    public function __construct()
    {
        $this->messageService = new MessageService();
        $this->userSocialModel = new UserSocialModel();
    }

    /**
     * ตรวจสอบความถูกต้องของ Webhook ตามข้อกำหนดเฉพาะของแต่ละแพลตฟอร์ม
     */
    public function verifyWebhook()
    {
        $hubMode = $this->request->getGet('hub_mode');
        $hubVerifyToken = $this->request->getGet('hub_verify_token');
        $hubChallenge = $this->request->getGet('hub_challenge');

        if ($hubMode === 'subscribe' && $hubVerifyToken === 'HAPPY') {
            // for fb
            // return $this->response->setStatusCode(ResponseInterface::HTTP_OK)->setBody($hubChallenge);

            // for whats app
            echo $hubChallenge; // ส่ง Challenge กลับไป
            http_response_code(200);
            exit;
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
    }

    /**
     * จัดการข้อมูล Webhook จากแพลตฟอร์มต่าง ๆ
     */
    public function webhook($userSocialID)
    {
        $input = $this->request->getJSON();

        try {

            // Facebook
            if (isset($input->object) && $input->object == 'page') {
                $userSocial = $this->userSocialModel->getUserSocialByPageID('Facebook', $input->entry[0]->id);
            }

            // Whats App
            else if (isset($input->object) && $input->object == 'whatsapp_business_account') {
                $userSocial = $this->userSocialModel->getUserSocialByPageID('WhatsApp', $input->entry[0]->id);
            }

            // Instagram
            else if (isset($input->object) && $input->object == 'instagram') {
                $userSocial = $this->userSocialModel->getUserSocialByPageID('Instagram', $input->entry[0]->id);
            }

            // Line 
            else {
                $userSocial = $this->userSocialModel->getUserSocialByID(hashidsDecrypt($userSocialID));
            }

            $handler = HandlerFactory::createHandler($userSocial->platform, $this->messageService);
            log_message('info', "ข้อความเข้า Webhook {$userSocial->platform}: " . json_encode($input, JSON_PRETTY_PRINT));
            $handler->handleWebhook($input, $userSocial);

            // กรณีเปิดใช้งานให้ AI ช่วยตอบ
            if ($userSocial->ai === 'on') $handler->handleReplyByAI($input, $userSocial); // TODO:: HANDLE

            return $this->response->setJSON(['status' => 'success']);
        } catch (\InvalidArgumentException $e) {
            log_message('error', "WebhookController error: " . $e->getMessage());
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
