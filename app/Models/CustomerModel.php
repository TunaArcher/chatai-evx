<?php

namespace App\Models;

use CodeIgniter\Database\ConnectionInterface;

class CustomerModel
{

    protected $db;

    public function __construct()
    {
        $db = \Config\Database::connect();
        $this->db = &$db;
    }

    public function getCustomerAll()
    {
        $builder = $this->db->table('customers');

        return $builder
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResult();
    }

    public function getCustomerByID($id)
    {
        $builder = $this->db->table('customers');

        return $builder->where('id', $id)->get()->getRow();
    }

    public function insertCustomer($data)
    {
        $builder = $this->db->table('customers');

        return $builder->insert($data) ? $this->db->insertID() : false;
    }

    public function updateCustomerByID($id, $data)
    {
        $builder = $this->db->table('customers');

        return $builder->where('id', $id)->update($data);
    }

    public function deleteCustomerByID($id)
    {
        $builder = $this->db->table('customers');

        return $builder->where('id', $id)->delete();
    }

    public function getCustomer($Customername)
    {
        $builder = $this->db->table('customers');
        
        return $builder->where('Customername', $Customername)->get()->getResult();
    }

    public function getCustomerByUIDAndPlatform($UID, $platform)
    {
        $builder = $this->db->table('customers');

        return $builder->where('uid', $UID)->where('platform', $platform)->get()->getRow();
    }

    public function insertMessageSetting($data)
    {
        $builder = $this->db->table('message_setting');

        return $builder->insert($data) ? $this->db->insertID() : false;
    }

    public function updateMessageSetting($id, $data)
    {
        $builder = $this->db->table('message_setting');

        return $builder->where('user_id', $id)->update($data);
    }

    public function insertMessageTraning($data)
    {
        $builder = $this->db->table('message_setting_training');

        return $builder->insert($data) ? $this->db->insertID() : false;
    }

    public function getMessageToPromt($user_id)
    {
        $builder = $this->db->table('message_setting_training');
        return $builder->where('user_id', $user_id)->where('message_state', 'Q')->get()->getResult();
    }

    public function getMessageTraningByID($user_id)
    {
        $builder = $this->db->table('message_setting_training');
        return $builder->where('user_id', $user_id)->get()->getResult();
    }

    public function getMessageSettingByID($user_id)
    {
        $builder = $this->db->table('message_setting');
        return $builder->where('user_id', $user_id)->get()->getRow();
    }

    public function deletesMessageTraining($user_id)
    {
        $builder = $this->db->table('message_setting_training');

        return $builder->where('user_id', $user_id)->delete();
    }
}
