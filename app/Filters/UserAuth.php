<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

use Hashids\Hashids;

class UserAuth implements FilterInterface
{

    protected $userModel;
    protected $subscriptionModel;

    public function __construct()
    {
        $this->userModel = new \App\Models\UserModel();
        $this->subscriptionModel = new \App\Models\SubscriptionModel();
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        // ตรวจสอบการเข้าสู่ระบบ
        if (!session()->get('isUserLoggedIn')) {
            session()->setFlashdata(['session_expired' => 'เซ็นซันหมดอายุ กรุณาล็อคอินอีกครั้ง']);
            return redirect()->to('/login');
        }

        // ตรวจสอบว่าผู้ใช้มีอยู่ในระบบหรือไม่
        $userID = $this->decryptUserID(session()->get('userID'));
        $user = $this->userModel->getUserByID($userID);
        if (!$user) {
            session()->setFlashdata(['session_expired' => 'เซ็นซันหมดอายุ กรุณาล็อคอินอีกครั้ง']);
            return redirect()->to('/login');
        }

        // ตรวจสอบสถานะ Subscription
        $this->checkUserSubscription($userID);

        return null; // อนุญาตให้ผ่าน
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }

    /**
     * แกะรหัส User ID จาก Hashids
     */
    protected function decryptUserID($input)
    {
        $hashids = new Hashids(getenv('CLIENT_SECRET_KEY'));
        return $hashids->decode("$input")[0] ?? null;
    }

    /**
     * ตรวจสอบสถานะ Subscription ของผู้ใช้
     */
    protected function checkUserSubscription($userID)
    {
        $userSubscription = $this->subscriptionModel->getUserSubscription($userID);

        if ($userSubscription) {

            // อัปเดตสถานะ Subscription ใน Session
            $this->updateSubscriptionSession($userSubscription);

            // ตรวจสอบว่าหมดอายุหรือยังไม่ Active
            if ($this->isSubscriptionExpiredOrInactive($userSubscription)) {
                session()->set('subscription_status', $userSubscription->status);
            }
        }
    }

    /**
     * อัปเดตสถานะ Subscription ใน Session
     */
    protected function updateSubscriptionSession($subscription)
    {
        session()->set([
            'subscription_status' => $subscription->status,
            'subscription_current_period_start' => $subscription->current_period_start,
            'subscription_current_period_end' => $subscription->current_period_end,
            'subscription_cancel_at_period_end' => $subscription->cancel_at_period_end
        ]);
    }

    /**
     * ตรวจสอบว่า Subscription หมดอายุหรือยังไม่ Active
     */
    protected function isSubscriptionExpiredOrInactive($subscription)
    {
        return $subscription->current_period_end < time() || $subscription->status !== 'active';
    }
}
