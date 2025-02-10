<?php

namespace App\Controllers;

use App\Factories\HandlerFactory;
use App\Models\MessageModel;
use App\Models\MessageRoomModel;
use App\Models\SubscriptionModel;
use App\Models\UserModel;
use App\Models\UserSocialModel;
use App\Services\MessageService;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\RabbitMQPublisher;

class WebhookController extends BaseController
{
    private MessageService $messageService;

    private UserModel $userModel;
    private UserSocialModel $userSocialModel;
    private SubscriptionModel $subscriptionModel;

    private RabbitMQPublisher $rabbitMQPublisher;

    public function __construct()
    {
        $this->messageService = new MessageService();

        $this->userModel = new UserModel();
        $this->userSocialModel = new UserSocialModel();
        $this->subscriptionModel = new SubscriptionModel();

        $this->rabbitMQPublisher = new RabbitMQPublisher();
    }

    /**
     * ตรวจสอบความถูกต้องของ Webhook ตามข้อกำหนดเฉพาะของแต่ละแพลตฟอร์ม
     */
    public function verifyWebhook($slug)
    {
        $hubMode = $this->request->getGet('hub_mode');
        $hubVerifyToken = $this->request->getGet('hub_verify_token');
        $hubChallenge = $this->request->getGet('hub_challenge');

        switch ($slug) {
            case 'facebook':
                if ($hubVerifyToken === 'HAPPY_FACEBOOK') {
                    // ตอบกลับสำหรับ Facebook
                    return $this->response
                        ->setStatusCode(ResponseInterface::HTTP_OK)
                        ->setBody($hubChallenge);
                }
                break;

            case 'instagram':
                if ($hubVerifyToken === 'HAPPY_INSTAGRAM') {
                    // ตอบกลับสำหรับ Instagram
                    return $this->response
                        ->setStatusCode(ResponseInterface::HTTP_OK)
                        ->setBody($hubChallenge);
                }
                break;

            case 'whatsapp':
                if ($hubVerifyToken === 'HAPPY_WHATSAPP') {
                    // ตอบกลับสำหรับ WhatsApp
                    echo $hubChallenge; // ส่ง Challenge กลับไป
                    http_response_code(200);
                    exit;
                }
                break;
            default:
                break;
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
    }

    /**
     * จัดการข้อมูล Webhook จากแพลตฟอร์มต่าง ๆ
     */
    public function webhook($slug)
    {
        $input = $this->request->getJSON();

        // debug
        // log_message('info', "ข้อความเข้า Webhook  " . json_encode($input, JSON_PRETTY_PRINT));

        try {
            // กำหนด $userSocial ไว้ใน scope เพื่อใช้งานในภายหลัง
            $userSocial = null;

            switch ($slug) {
                case 'facebook':
                    $userSocial = $this->handleFacebook($input);
                    break;

                case 'instagram':
                    $userSocial = $this->handleInstagram($input);
                    break;

                case 'whatsapp':
                    $userSocial = $this->handleWhatsApp($input);
                    break;

                default:
                    $userSocial = $this->handleLine($slug);
                    break;
            }

            // ดำเนินการหากพบ $userSocial
            if ($userSocial) {

                log_message('info', "ข้อความเข้า Webhook {$userSocial->platform}: " . json_encode($input, JSON_PRETTY_PRINT));

                $handler = HandlerFactory::createHandler($userSocial->platform, $this->messageService);
                $messageRoom = $handler->handleWebhook($input, $userSocial);

                // กรณีเปิดใช้งานให้ AI ช่วยตอบ
                if ($userSocial->ai === 'on' && $messageRoom) {

                    $user = $this->userModel->getUserByID($userSocial->user_id);
                    $subscription = $this->subscriptionModel->getUserSubscription($user->id);

                    if (!$subscription) {
                        if ($user->free_request_limit < 10) {
                            // ส่งข้อความไปที่ RabbitMQ แทนการรอ 5 วินาที
                            $this->rabbitMQPublisher->publishMessage($messageRoom, $userSocial);
                            $this->userModel->updateUserByID($user->id, [
                                'free_request_limit' => $user->free_request_limit + 1
                            ]);
                        }
                    } elseif ($subscription->status == 'active' && $subscription->current_period_end > time()) {
                        // ส่งข้อความไปที่ RabbitMQ แทนการรอ 5 วินาที
                        $this->rabbitMQPublisher->publishMessage($messageRoom, $userSocial);
                    }
                }

                return $this->response->setJSON(['status' => 'success']);
            }
        } catch (\InvalidArgumentException $e) {

            log_message('error', "WebhookController error: " . $e->getMessage());

            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * จัดการ Webhook สำหรับ Facebook
     */
    private function handleFacebook($input)
    {
        if (getenv('CI_ENVIRONMENT') == 'development') {
            return $this->userSocialModel->getUserSocialByPageID('Facebook', '1741273556202429');
        }

        if (isset($input->object) && $input->object == 'page') {
            return $this->userSocialModel->getUserSocialByPageID('Facebook', $input->entry[0]->id);
        }
        return null;
    }

    /**
     * จัดการ Webhook สำหรับ Instagram
     */
    private function handleInstagram($input)
    {
        if (isset($input->object) && $input->object == 'instagram') {
            return $this->userSocialModel->getUserSocialByPageID('Instagram', $input->entry[0]->id);
        }
        return null;
    }

    /**
     * จัดการ Webhook สำหรับ WhatsApp
     */
    private function handleWhatsApp($input)
    {
        if (isset($input->object) && $input->object == 'whatsapp_business_account') {
            return $this->userSocialModel->getUserSocialByPageID('WhatsApp', $input->entry[0]->id);
        }
        return null;
    }

    /**
     * จัดการ Webhook สำหรับ Line
     */
    private function handleLine($slug)
    {
        $userSocialID = $slug;
        return $this->userSocialModel->getUserSocialByID(hashidsDecrypt($userSocialID));
    }

    /**
     * จัดการการตอบกลับโดย AI
     */
    private function handleAIResponse($messageRoom, $userSocial)
    {
        $user = $this->userModel->getUserByID($userSocial->user_id);

        if (!$user) throw new \InvalidArgumentException('User not found');

        $subscription = $this->subscriptionModel->getUserSubscription($user->id);

        if (!$subscription) {
            if ($user->free_request_limit <= 10) {
                $handler = HandlerFactory::createHandler($userSocial->platform, $this->messageService);
                $handler->handleReplyByAI($messageRoom, $userSocial);
                $this->userModel->updateUserByID($user->id, [
                    'free_request_limit' => $user->free_request_limit + 1
                ]);
            }
        } elseif ($subscription->status == 'active' && $subscription->current_period_end > time()) {
            $handler = HandlerFactory::createHandler($userSocial->platform, $this->messageService);
            $handler->handleReplyByAI($messageRoom, $userSocial);
        }
    }
}
