<?php

namespace App\Models;

use CodeIgniter\Database\ConnectionInterface;

class PurchaseModel
{

    protected $db;

    public function __construct()
    {
        $db = \Config\Database::connect();
        $this->db = &$db;
    }

    public function getPurchasesAll()
    {
        $builder = $this->db->table('purchases');

        return $builder
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResult();
    }

    public function getPurchasesByID($id)
    {
        $builder = $this->db->table('purchases');

        return $builder->where('id', $id)->get()->getRow();
    }

    public function insertPurchases($data)
    {
        $builder = $this->db->table('purchases');

        return $builder->insert($data) ? $this->db->insertID() : false;
    }

    public function updatePurchasesByID($id, $data)
    {
        $builder = $this->db->table('purchases');

        return $builder->where('id', $id)->update($data);
    }

    public function deletePurchasesByID($id)
    {
        $builder = $this->db->table('purchases');

        return $builder->where('id', $id)->delete();
    }

    public function getPurchasesByUserID($userID)
    {
        $builder = $this->db->table('purchases');

        return $builder
            ->where('user_id', $userID) 
            ->where('deleted_at', null)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResult();
    }

    public function getPurchasesByPlatformAndToken($platform, $data)
    {

        $builder = $this->db->table('purchases');

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

    public function getPurchasesByPageID($platform, $pageID)
    {
        $builder = $this->db->table('purchases');

        return $builder
        ->where('platform', $platform)
        ->where('page_id', $pageID)
        ->get()
        ->getRow();
    }
}
