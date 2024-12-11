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

    public function getUserSocialByUserID($userID)
    {
        $builder = $this->db->table('user_socials');

        return $builder
            ->where('deleted_at', null) // เพิ่มเงื่อนไขสำหรับ deleted_at เป็น NULL
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResult();
    }

    public function getUserSocialByPlatformAndToken($platform, $data)
    {

        $builder = $this->db->table('user_socials');

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
}
