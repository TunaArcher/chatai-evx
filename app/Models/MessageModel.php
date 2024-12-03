<?php

namespace App\Models;

use CodeIgniter\Database\ConnectionInterface;

class MessageModel
{

    protected $db;

    public function __construct()
    {
        $db = \Config\Database::connect();
        $this->db = &$db;
    }

    public function getMessageAll()
    {
        $builder = $this->db->table('messages');

        return $builder
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResult();
    }

    public function getMessageByID($id)
    {
        $builder = $this->db->table('messages');

        return $builder->where('id', $id)->get()->getRow();
    }

    public function insertMessage($data)
    {
        $builder = $this->db->table('messages');

        return $builder->insert($data) ? $this->db->insertID() : false;
    }

    public function updateMessageByID($id, $data)
    {
        $builder = $this->db->table('messages');

        return $builder->where('id', $id)->update($data);
    }

    public function deleteMessageByID($id)
    {
        $builder = $this->db->table('messages');

        return $builder->where('id', $id)->delete();
    }

    public function getMessage($Messagename)
    {
        $builder = $this->db->table('messages');
        return $builder->where('Messagename', $Messagename)->get()->getResult();
    }

    public function getLastMessageByRoomID($roomID)
    {
        $sql = "
            SELECT * FROM messages
            WHERE room_id = $roomID
            ORDER BY created_at DESC LIMIT 1
        ";

        $builder = $this->db->query($sql);

        return $builder->getRow();
    }

    public function getMessageRoomByRoomID($roomID)
    {
        $sql = "
            SELECT * FROM messages
            WHERE room_id = '$roomID'
        ";

        $builder = $this->db->query($sql);

        return $builder->getResult();
    }
}
