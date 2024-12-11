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
    public function verifyWebhook($userSocialID)
    {
        $hubMode = $this->request->getGet('hub_mode');
        $hubVerifyToken = $this->request->getGet('hub_verify_token');
        $hubChallenge = $this->request->getGet('hub_challenge');

        if ($hubMode === 'subscribe' && $hubVerifyToken === 'HAPPY') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_OK)->setBody($hubChallenge);
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
    }

    /**
     * จัดการข้อมูล Webhook จากแพลตฟอร์มต่าง ๆ
     */
    public function webhook($userSocialID)
    {
        $input = $this->request->getJSON();
        $userSocial = $this->userSocialModel->getUserSocialByID(hashidsDecrypt($userSocialID));
  
        try {
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
