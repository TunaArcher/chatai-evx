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

    public function getMessageRoomByRoomID($roomID, $status = '')
    {
        $sql = "
            SELECT * FROM messages
            WHERE room_id = '$roomID'
        ";

        switch ($status) {
            case 'MANUL':
                $sql .= " AND reply_by = 'MANUAL'";
                break;
            case 'AI':
                $sql .= " AND reply_by = 'AI'";
                break;
        }

        $builder = $this->db->query($sql);

        return $builder->getResult();
    }

    public function lastContextTimestamp($messageRoomID)
    {
        $sql = "
            SELECT MAX(created_at) AS _time
            FROM messages 
            WHERE room_id = $messageRoomID AND is_context = '1'
        ";

        $builder = $this->db->query($sql);

        return $builder->getRow();
    }

    public function newContextCount($messageRoomID, $last_timestamp)
    {

        $sql = "
            SELECT COUNT(*) AS _count
            FROM messages 
            WHERE room_id = $messageRoomID AND is_context = '1' AND created_at > '$last_timestamp'
        ";
        
        // $sql = "
        //     SELECT COUNT(*) AS _count
        //     FROM messages 
        //     WHERE room_id = 112 AND is_context = '1' AND created_at > '2025-01-29 12:48:00'
        // ";

        $builder = $this->db->query($sql);

        return $builder->getRow();
    }

    public function clearUserContext($messageRoomID)
    {
        $sql = "
            UPDATE messages
            SET is_context = '0'
            WHERE room_id = $messageRoomID AND is_context = '1'
        ";

        return $this->db->query($sql);
    }

    public function getMessageNotReplyBySendByAndRoomID($sendBy, $roomID)
    {
        $sql = "
            SELECT * FROM messages
            WHERE send_by = '$sendBy' AND room_id = '$roomID' AND is_context = '1'
        ";

        $builder = $this->db->query($sql);

        return $builder->getResult();
    }

    public function getHistoryMessageByRoomID($roomID, $limit)
    {
        $sql = "
            SELECT * FROM (
                SELECT * FROM messages
                WHERE room_id = $roomID
                ORDER BY id DESC
                LIMIT $limit
            ) sub
            ORDER BY id ASC;
        ";

        $builder = $this->db->query($sql);

        return $builder->getResult();
    }
}
