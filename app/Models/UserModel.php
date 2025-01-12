<?php

namespace App\Models;

use CodeIgniter\Database\ConnectionInterface;

class UserModel
{

    protected $db;

    public function __construct()
    {
        $db = \Config\Database::connect();
        $this->db = &$db;
    }

    public function getUserAll()
    {
        $builder = $this->db->table('users');

        return $builder
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResult();
    }

    public function getUserByID($id)
    {
        $builder = $this->db->table('users');
        return $builder->where('id', $id)->get()->getRow();
    }

    public function getMessageTraningByID($id)
    {
        $builder = $this->db->table('message_setting');
        return $builder->where('user_id', $id)->get()->getRow();
    }

    public function insertUser($data)
    {
        $builder = $this->db->table('users');

        return $builder->insert($data) ? $this->db->insertID() : false;
    }

    public function updateUserByID($id, $data)
    {
        $builder = $this->db->table('users');

        return $builder->where('id', $id)->update($data);
    }

    public function deleteUserByID($id)
    {
        $builder = $this->db->table('users');

        return $builder->where('id', $id)->delete();
    }

    public function getUser($Username)
    {
        $builder = $this->db->table('users');
        return $builder->where('Username', $Username)->get()->getResult();
    }

    public function getUserByPlatFromAndID($platform, $platformUserID)
    {
        $builder = $this->db->table('users');

        return $builder
            ->where('sign_by_platform', $platform)
            ->where('platform_user_id', $platformUserID)
            ->get()
            ->getRow();
    }
}