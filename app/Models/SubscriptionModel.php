<?php

namespace App\Models;

use CodeIgniter\Database\ConnectionInterface;

class SubscriptionModel
{

    protected $db;

    public function __construct()
    {
        $db = \Config\Database::connect();
        $this->db = &$db;
    }

    public function getSubscriptionAll()
    {
        $builder = $this->db->table('subscriptions');

        return $builder
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResult();
    }

    public function getSubscriptionByID($id)
    {
        $builder = $this->db->table('subscriptions');

        return $builder->where('id', $id)->get()->getRow();
    }

    public function insertSubscription($data)
    {
        $builder = $this->db->table('subscriptions');

        return $builder->insert($data) ? $this->db->insertID() : false;
    }

    public function updateSubscriptionByID($id, $data)
    {
        $builder = $this->db->table('subscriptions');

        return $builder->where('id', $id)->update($data);
    }

    public function deleteSubscriptionByID($id)
    {
        $builder = $this->db->table('subscriptions');

        return $builder->where('id', $id)->delete();
    }

    public function getSubscriptionByUserID($userID)
    {
        $builder = $this->db->table('subscriptions');

        return $builder
            ->where('user_id', $userID)
            ->where('deleted_at', null)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResult();
    }

    public function getSubscriptionByPlatformAndToken($platform, $data)
    {

        $builder = $this->db->table('subscriptions');

        switch ($platform) {
            case 'Facebook':

                return false;

                break;

            case 'Line':

                return $builder
                    ->where('platform', $platform)
                    ->where('line_channel_id', $data['line_channel_id'])
                    ->where('line_channel_secret', $data['line_channel_secret'])
                    ->get()
                    ->getRow();

                break;

            case 'WhatsApp':

                return $builder
                    ->where('platform', $platform)
                    ->where('whatsapp_token', $data['whatsapp_token'])
                    // ->where('whatsapp_phone_number_id', $data['whatsapp_phone_number_id'])
                    ->get()
                    ->getRow();

                break;

            case 'Instagram':
                break;

            case 'Tiktok':
                break;
        }
    }

    public function getSubscriptionByPageID($platform, $pageID)
    {
        $builder = $this->db->table('subscriptions');

        return $builder
            ->where('platform', $platform)
            ->where('page_id', $pageID)
            ->get()
            ->getRow();
    }

    public function getSubscriptionByStripeCustomerID($stripeCustomerID)
    {
        $builder = $this->db->table('subscriptions');

        return $builder
            ->where('stripe_customer_id', $stripeCustomerID)
            ->get()
            ->getRow();
    }

    public function isUserSubscription($userID)
    {
        $sql = "
            SELECT 
                users.email, 
                subscription_plans.name, 
                subscriptions.status,
                subscriptions.current_period_start,
                subscriptions.current_period_end,
                subscriptions.cancel_at_period_end
            FROM users
            JOIN subscriptions ON users.id = subscriptions.user_id
            JOIN subscription_plans ON subscriptions.subscription_plan_id = subscription_plans.id
            WHERE users.id = $userID AND subscriptions.status = 'active'
        ";

        $builder = $this->db->query($sql);

        return $builder->getRow();
    }

    public function getUserSubscription($userID)
    {
        $sql = "
            SELECT 
                users.email, 
                subscription_plans.name, 
                subscriptions.status,
                subscriptions.current_period_start,
                subscriptions.current_period_end,
                subscriptions.cancel_at_period_end
            FROM users
            JOIN subscriptions ON users.id = subscriptions.user_id
            JOIN subscription_plans ON subscriptions.subscription_plan_id = subscription_plans.id
            WHERE users.id = $userID
        ";

        $builder = $this->db->query($sql);

        return $builder->getRow();
    }
}
