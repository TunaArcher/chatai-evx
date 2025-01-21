<?php

namespace App\Models;

use CodeIgniter\Database\ConnectionInterface;

class UserAccountModel
{

    protected $db;

    public function __construct()
    {
        $db = \Config\Database::connect();
        $this->db = &$db;
    }

    public function getUserAccountAll()
    {
        $builder = $this->db->table('user_accounts');

        return $builder
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResult();
    }

    public function getUserAccountByID($id)
    {
        $builder = $this->db->table('user_accounts');

        return $builder->where('id', $id)->get()->getRow();
    }

    public function insertUserAccount($data)
    {
        $builder = $this->db->table('user_accounts');

        return $builder->insert($data) ? $this->db->insertID() : false;
    }

    public function updateUserAccountByID($id, $data)
    {
        $builder = $this->db->table('user_accounts');

        return $builder->where('id', $id)->update($data);
    }

    public function deleteUserAccountByID($id)
    {
        $builder = $this->db->table('user_accounts');

        return $builder->where('id', $id)->delete();
    }

    public function getUserAccount($Username)
    {
        $builder = $this->db->table('user_accounts');
        return $builder->where('Username', $Username)->get()->getResult();
    }

    public function getUserAccountByProviderAndProviderUserID($provider, $providerUserID)
    {
        $builder = $this->db->table('user_accounts');

        return $builder
            ->where('provider', $provider)
            ->where('provider_user_id', $providerUserID)
            ->get()
            ->getRow();
    }

    public function getUserAccountByEmail($email)
    {
        $builder = $this->db->table('user_accounts');
        return $builder->where('email', $email)->get()->getRow();
    }

    public function getUserAccountByUserOwnerID($userOwnerID)
    {
        $builder = $this->db->table('user_accounts');

        return $builder->where('user_owner_id', $userOwnerID)->get()->getResult();
    }

    public function getUserAccountByStripeCustomerID($stripeCustomerID)
    {
        $builder = $this->db->table('user_accounts');

        return $builder
            ->where('stripe_customer_id', $stripeCustomerID)
            ->get()
            ->getRow();
    }

    public function updateUserAccount($data)
    {
        $builder = $this->db->table('user_accounts');

        return $builder->update($data);
    }
}
