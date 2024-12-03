<?php

namespace App\Models;

use CodeIgniter\Database\ConnectionInterface;

class MessageRoomModel
{

    protected $db;

    public function __construct()
    {
        $db = \Config\Database::connect();
        $this->db = &$db;
    }

    public function getMessageRoomAll()
    {
        $builder = $this->db->table('message_rooms');

        return $builder
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResult();
    }

    public function getMessageRoomByID($id)
    {
        $builder = $this->db->table('message_rooms');

        return $builder->where('id', $id)->get()->getRow();
    }

    public function insertMessageRoom($data)
    {
        $builder = $this->db->table('message_rooms');

        return $builder->insert($data) ? $this->db->insertID() : false;
    }

    public function updateMessageRoomByID($id, $data)
    {
        $builder = $this->db->table('message_rooms');

        return $builder->where('id', $id)->update($data);
    }

    public function deleteMessageRoomByID($id)
    {
        $builder = $this->db->table('message_rooms');

        return $builder->where('id', $id)->delete();
    }

    public function getMessageRoom($MessageRoomname)
    {
        $builder = $this->db->table('message_rooms');
        return $builder->where('MessageRoomname', $MessageRoomname)->get()->getResult();
    }

    public function getMessageRoomByUserID($userID) 
    {
        $sql = "
            SELECT * FROM message_rooms
            WHERE user_id = '$userID'
        ";

        $builder = $this->db->query($sql);

        return $builder->getResult();
    }

}