<?php

namespace App\Models;

use CodeIgniter\Database\ConnectionInterface;

class UserSocialModel
{

    protected $db;

    public function __construct()
    {
        $db = \Config\Database::connect();
        $this->db = &$db;
    }

    public function getUserSocialAll()
    {
        $builder = $this->db->table('user_socials');

        return $builder
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResult();
    }

    public function getUserSocialByID($id)
    {
        $builder = $this->db->table('user_socials');

        return $builder->where('id', $id)->get()->getRow();
    }

    public function insertUserSocial($data)
    {
        $builder = $this->db->table('user_socials');

        return $builder->insert($data) ? $this->db->insertID() : false;
    }

    public function updateUserSocialByID($id, $data)
    {
        $builder = $this->db->table('user_socials');

        return $builder->where('id', $id)->update($data);
    }

    public function deleteUserSocialByID($id)
    {
        $builder = $this->db->table('user_socials');

        return $builder->where('id', $id)->delete();
    }
}
