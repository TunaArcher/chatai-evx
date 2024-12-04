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
            SELECT 
                mr.*,
                m.created_at AS latest_message_time
            FROM 
                message_rooms mr
            LEFT JOIN 
                messages m ON mr.id = m.room_id
            WHERE 
                mr.user_id = '$userID'
                AND m.created_at = (
                    SELECT MAX(created_at)
                    FROM messages
                    WHERE room_id = mr.id
                )
            ORDER BY 
                m.created_at DESC
        ";

        $builder = $this->db->query($sql);

        return $builder->getResult();
    }

    public function getMessageRoomByCustomerID($customerID)
    {
        $builder = $this->db->table('message_rooms');

        return $builder->where('customer_id', $customerID)->get()->getRow();
    }
}
