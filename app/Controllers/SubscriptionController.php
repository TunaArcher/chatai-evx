<?php

namespace App\Controllers;

use App\Models\SubscriptionModel;
use App\Models\SubscriptionPlanModel;
use App\Models\UserModel;

use Ramsey\Uuid\Uuid;

class SubscriptionController extends BaseController
{

    private SubscriptionModel $subscriptionModel;
    private SubscriptionPlanModel $subscriptionPlanModel;
    private UserModel $userModel;

    public function __construct()
    {
        $this->subscriptionModel = new SubscriptionModel();
        $this->subscriptionPlanModel = new SubscriptionPlanModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $data['content'] = 'package/index';
        $data['title'] = 'Package';

        echo view('/app', $data);
    }

    public function selectPlan()
    {
        $response = [
            'success' => 0,
            'message' => '',
        ];

        $status = 500;

        try {

            $data = $this->request->getJSON();

            $userID = hashidsDecrypt($data->userID);

            $user = $this->userModel->getUserByID($userID);
            if (!$user) throw new \Exception('ไม่พบยูส');

            $isUserSubscription = $this->subscriptionModel->isUserSubscription($user->id);
            if ($isUserSubscription) throw new \Exception('คุณมี Subscription อยู่แล้ว');

            $subscriptionPlan = $this->subscriptionPlanModel->getSubscriptionPlansByID($data->planID);

            if (!$subscriptionPlan) throw new \Exception('ไม่พบ Plan ที่เลือก');

            $stripeService = new \App\Libraries\StripeService();

            $newUser = false;

            // ตรวจสอบว่า user มี stripe_customer_id แล้วหรือไม่
            if ($user->stripe_customer_id == null || $user->stripe_customer_id == '') {
                
                $userStripe = $stripeService->createCustomer($user->name, $user->email);

                $this->userModel->updateUserByID($user->id, [
                    'stripe_customer_id' => $userStripe->id
                ]);

                $newUser = true;
            }

            $uuid = Uuid::uuid4()->toString();

            $checkoutSession = $stripeService->createCheckoutSession(
                $newUser ? $userStripe->id : $user->stripe_customer_id,
                $subscriptionPlan->stripe_price_id,
                base_url('/payment/success?session_id=' . $uuid),
                base_url('/payment/cancel'),
                [
                    'user_id' => hashidsEncrypt($user->id),
                    'subscription_plan_id' => hashidsEncrypt($subscriptionPlan->id),
                ]
            );

            // log_message('info', 'ก่อนชำระเงิน ' . $checkoutSession);

            $response = [
                'success' => 1,
                'message' => 'success',
                'url' => $checkoutSession->url,
            ];

            $status = 200;

            return $this->response
                ->setStatusCode($status)
                ->setContentType('application/json')
                ->setJSON($response);
        } catch (\Exception $e) {
            log_message('error', 'SubscriptionController::selectPlan error {message}', ['message' => $e->getMessage()]);
            $response['message'] = $e->getMessage();
            return $this->response
                ->setStatusCode($status)
                ->setContentType('application/json')
                ->setJSON($response);
        }
    }

    public function handlePlan()
    {

        $response = [
            'success' => 0,
            'message' => '',
        ];

        $status = 500;

        try {

            $data = $this->request->getJSON();

            $userID = hashidsDecrypt($data->userID);

            $user = $this->userModel->getUserByID($userID);
            if (!$user) throw new \Exception('ไม่พบยูส');

            $stripeService = new \App\Libraries\StripeService();

            $billingPortalSession = $stripeService->createBillingPortalSession($user->stripe_customer_id);

            $response = [
                'success' => 1,
                'message' => 'success',
                'url' => $billingPortalSession->url,
            ];

            $status = 200;

            return $this->response
                ->setStatusCode($status)
                ->setContentType('application/json')
                ->setJSON($response);
        } catch (\Exception $e) {
            log_message('error', 'SubscriptionController::handlePlan error {message}', ['message' => $e->getMessage()]);
            $response['message'] = $e->getMessage();
            return $this->response
                ->setStatusCode($status)
                ->setContentType('application/json')
                ->setJSON($response);
        }
    }
}
