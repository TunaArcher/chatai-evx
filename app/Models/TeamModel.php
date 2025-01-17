<?php

namespace App\Models;

use CodeIgniter\Database\ConnectionInterface;

class TeamModel
{

    protected $db;

    public function __construct()
    {
        $db = \Config\Database::connect();
        $this->db = &$db;
    }

    public function getTeamAll()
    {
        $builder = $this->db->table('teams');

        return $builder
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResult();
    }

    public function getTeamByID($id)
    {
        $builder = $this->db->table('teams');
        return $builder->where('id', $id)->get()->getRow();
    }

    public function getMessageTraningByID($id)
    {
        $builder = $this->db->table('message_setting');
        return $builder->where('Team_id', $id)->get()->getRow();
    }

    public function insertTeam($data)
    {
        $builder = $this->db->table('teams');

        return $builder->insert($data) ? $this->db->insertID() : false;
    }

    public function updateTeamByID($id, $data)
    {
        $builder = $this->db->table('teams');

        return $builder->where('id', $id)->update($data);
    }

    public function deleteTeamByID($id)
    {
        $builder = $this->db->table('teams');

        return $builder->where('id', $id)->delete();
    }

    public function getTeam($Teamname)
    {
        $builder = $this->db->table('teams');
        return $builder->where('Teamname', $Teamname)->get()->getResult();
    }

    public function getTeamByPlatFromAndID($platform, $platformTeamID)
    {
        $builder = $this->db->table('teams');

        return $builder
            ->where('sign_by_platform', $platform)
            ->where('platform_Team_id', $platformTeamID)
            ->get()
            ->getRow();
    }

    public function getTeamByEmail($email)
    {
        $builder = $this->db->table('teams');
        
        return $builder->where('email', $email)->get()->getRow();
    }

    public function getTeamByOwnerID($ownerID)
    {
        $builder = $this->db->table('teams');

        return $builder
            ->where('owner_id', $ownerID)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResult();
    }

    public function updateRelations($teamId, $connectIds, $memberIds)
    {
        $builderSocials = $this->db->table('team_socials');
        $builderMembers = $this->db->table('team_members');

        // 1. ลบข้อมูลเก่าของทีม
        $builderSocials->where('team_id', $teamId)->delete();
        $builderMembers->where('team_id', $teamId)->delete();

        // 2. เพิ่ม Connect IDs ใหม่
        if (!empty($connectIds)) {
            $connectData = [];
            foreach ($connectIds as $connectId) {
                $connectData[] = [
                    'team_id' => $teamId,
                    'social_id' => $connectId,
                ];
            }

            $builderSocials->insertBatch($connectData); // เพิ่มข้อมูลหลายรายการในครั้งเดียว
        }

        // 3. เพิ่ม Member IDs ใหม่
        if (!empty($memberIds)) {
            $memberData = [];
            foreach ($memberIds as $memberId) {
                $memberData[] = [
                    'team_id' => $teamId,
                    'user_id' => $memberId,
                ];
            }

            $builderMembers->insertBatch($memberData); // เพิ่มข้อมูลหลายรายการในครั้งเดียว
        }
    }
}
