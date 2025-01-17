<?php

namespace App\Controllers;

use App\Models\SubscriptionModel;
use App\Models\SubscriptionPlanModel;
use App\Models\PurchaseModel;
use App\Models\UserModel;

use Stripe\Stripe;

class StripeController extends BaseController
{

    private SubscriptionModel $subscriptionModel;
    private SubscriptionPlanModel $subscriptionPlanModel;
    private PurchaseModel $purchaseModel;
    private UserModel $userModel;

    public function __construct()
    {
        $this->subscriptionModel = new SubscriptionModel();
        $this->subscriptionPlanModel = new SubscriptionPlanModel();
        $this->purchaseModel = new PurchaseModel();
        $this->userModel = new UserModel();
    }

    public function webhook()
    {
        // Replace this endpoint secret with your endpoint's unique secret
        // If you are testing with the CLI, find the secret by running 'stripe listen'
        // If you are using an endpoint defined with the API or dashboard, look in your webhook settings
        // at https://dashboard.stripe.com/webhooks
        $endpointSecret = getenv('STRIPE_ENDPOINT_SECRET'); // กำหนดใน .env เช่น whsec_12345

        Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

        // อ่าน payload ที่ Stripe ส่งมา
        $payload = @file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? null;
        $event = null;

        try {
            // ตรวจสอบลายเซ็นของ Webhook
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $endpointSecret
            );
        } catch (\UnexpectedValueException $e) {
            // Payload ไม่ถูกต้อง
            log_message('info', "การตรวจสอบลายเซ็นของ Stripe Webhook Payload ไม่ถูกต้อง");
            return $this->response->setStatusCode(400)->setBody('⚠️ Invalid payload.');
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // ลายเซ็นไม่ถูกต้อง
            log_message('info', "การตรวจสอบลายเซ็นของ Stripe Webhook ลายเซ็นไม่ถูกต้อง");
            return $this->response->setStatusCode(400)->setBody('⚠️ Invalid signature.');
        }

        // จัดการกับ Event ที่ Stripe ส่งมา
        switch ($event->type) {
            case 'customer.subscription.trial_will_end':
                $subscription = $event->data->object; // \Stripe\Subscription
                $this->handleTrialWillEnd($subscription);
                break;

            case 'customer.subscription.created':
                log_message('info', "debug customer.subscription.created $event");
                $subscription = $event->data->object; // \Stripe\Subscription
                $this->handleSubscriptionCreated($subscription);
                // break;

            case 'customer.subscription.updated':
                log_message('info', "debug customer.subscription.updated $event");
                $subscription = $event->data->object; // \Stripe\Subscription
                $this->handleSubscriptionUpdated($subscription);

                // หา User
                $user = $this->userModel->getUserByStripeCustomerID($subscription->customer);

                // หา Subscription Plan จาก Stripe Price ID
                $subscriptionPlan = $this->subscriptionPlanModel->getSubscriptionPlansByStripePriceID($subscription->items->data[0]->plan->id);

                // หา Subscription จาก Stripe Subscription ID
                $getSubscription = $this->subscriptionModel->getSubscriptionByStripeCustomerID($subscription->customer);

                // insert new
                if (!$getSubscription) {
                    $this->subscriptionModel->insertSubscription([
                        'user_id' => $user->id,
                        'subscription_plan_id' => $subscriptionPlan->id,

                        'status' => $subscription->status,
                        'stripe_subscription_id' => $subscription->id,
                        'current_period_start' => $subscription->current_period_start,
                        'current_period_end' => $subscription->current_period_end,
                        'cancel_at_period_end' => $subscription->cancel_at_period_end,

                        'stripe_customer_id' => $user->stripe_customer_id,
                    ]);
                }

                // update old
                else {
                    log_message('info', "StripeController: ข้อมูลก่อน Update " . json_encode($getSubscription));
                    $this->subscriptionModel->updateSubscriptionByID($getSubscription->id, [
                        'subscription_plan_id' => $subscriptionPlan->id,

                        'status' => $subscription->status,
                        'stripe_subscription_id' => $subscription->id,
                        'current_period_start' => $subscription->current_period_start,
                        'current_period_end' => $subscription->current_period_end,
                        'cancel_at_period_end' => $subscription->cancel_at_period_end,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    $this->userModel->updateUserByID($user->id, [
                        'current_subscription_id' => $getSubscription->id,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }

                break;

            case 'customer.subscription.deleted':
                $subscription = $event->data->object; // \Stripe\Subscription
                $this->handleSubscriptionDeleted($subscription);
                break;
            case 'entitlements.active_entitlement_summary.updated':
                $subscription = $event->data->object; // \Stripe\Subscription
                $this->handleEntitlementUpdated($subscription);
                break;

            default:
                // กรณี Event Type ที่ไม่รู้จัก
                log_message('warning', "Received unknown event type: {$event->type}");
                return $this->response->setStatusCode(200);
        }

        return $this->response->setStatusCode(200)->setBody('Webhook handled successfully.');
    }

    // ตัวอย่างฟังก์ชันสำหรับจัดการ Event
    private function handleTrialWillEnd($subscription)
    {
        log_message('info', "Trial will end for subscription: {$subscription->id}");
        // เพิ่มโค้ดจัดการตามความต้องการของคุณ
    }

    private function handleSubscriptionCreated($subscription)
    {
        log_message('info', "Subscription created: {$subscription->id}");
        // เพิ่มโค้ดจัดการตามความต้องการของคุณ
    }

    private function handleSubscriptionDeleted($subscription)
    {
        log_message('info', "Subscription deleted: {$subscription->id}");
        // เพิ่มโค้ดจัดการตามความต้องการของคุณ
    }

    private function handleSubscriptionUpdated($subscription)
    {
        log_message('info', "Subscription updated: {$subscription->id}");
        // เพิ่มโค้ดจัดการตามความต้องการของคุณ
    }

    private function handleEntitlementUpdated($subscription)
    {
        log_message('info', "Entitlement updated: {$subscription->id}");
        // เพิ่มโค้ดจัดการตามความต้องการของคุณ
    }
}
