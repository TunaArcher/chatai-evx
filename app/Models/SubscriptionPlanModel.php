<?php

namespace App\Models;

use CodeIgniter\Database\ConnectionInterface;

class SubscriptionPlanModel
{

    protected $db;

    public function __construct()
    {
        $db = \Config\Database::connect();
        $this->db = &$db;
    }

    public function getSubscriptionPlansAll()
    {
        $builder = $this->db->table('subscription_plans');

        return $builder
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResult();
    }

    public function getSubscriptionPlansByID($id)
    {
        $builder = $this->db->table('subscription_plans');

        return $builder->where('id', $id)->get()->getRow();
    }

    public function insertSubscriptionPlans($data)
    {
        $builder = $this->db->table('subscription_plans');

        return $builder->insert($data) ? $this->db->insertID() : false;
    }

    public function updateSubscriptionPlansByID($id, $data)
    {
        $builder = $this->db->table('subscription_plans');

        return $builder->where('id', $id)->update($data);
    }

    public function deleteSubscriptionPlansByID($id)
    {
        $builder = $this->db->table('subscription_plans');

        return $builder->where('id', $id)->delete();
    }

    public function getSubscriptionPlansByUserID($userID)
    {
        $builder = $this->db->table('subscription_plans');

        return $builder
            ->where('user_id', $userID) 
            ->where('deleted_at', null)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResult();
    }

    public function getSubscriptionPlansByPlatformAndToken($platform, $data)
    {

        $builder = $this->db->table('subscription_plans');

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

    public function getSubscriptionPlansByPageID($platform, $pageID)
    {
        $builder = $this->db->table('subscription_plans');

        return $builder
        ->where('platform', $platform)
        ->where('page_id', $pageID)
        ->get()
        ->getRow();
    }

    public function getSubscriptionPlansByStripePriceID($stripePriceID)
    {
        $builder = $this->db->table('subscription_plans');

        return $builder
        ->where('stripe_price_id', $stripePriceID)
        ->get()
        ->getRow();
    }
}
