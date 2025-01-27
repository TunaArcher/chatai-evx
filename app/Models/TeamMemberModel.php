<?php

namespace App\Models;

use CodeIgniter\Database\ConnectionInterface;

class TeamMemberModel
{

    protected $db;

    public function __construct()
    {
        $db = \Config\Database::connect();
        $this->db = &$db;
    }

    public function getTeamMemberAll()
    {
        $builder = $this->db->table('team_members');

        return $builder
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResult();
    }

    public function getTeamMemberByID($id)
    {
        $builder = $this->db->table('team_members');

        return $builder->where('id', $id)->get()->getRow();
    }

    public function insertTeamMember($data)
    {
        $builder = $this->db->table('team_members');

        return $builder->insert($data) ? $this->db->insertID() : false;
    }

    public function updateTeamMemberByID($id, $data)
    {
        $builder = $this->db->table('team_members');

        return $builder->where('id', $id)->update($data);
    }

    public function deleteTeamMemberByID($id)
    {
        $builder = $this->db->table('team_members');

        return $builder->where('id', $id)->delete();
    }

    public function getTeamMemberByPlatFromAndID($platform, $platformTeamMemberID)
    {
        $builder = $this->db->table('team_members');

        return $builder
            ->where('sign_by_platform', $platform)
            ->where('platform_TeamMember_id', $platformTeamMemberID)
            ->get()
            ->getRow();
    }

    public function getTeamMemberByTeamID($teamID, $select = 'ALL')
    {
        if ($select == 'ONLY_ID') {
            $sql = "
                SELECT 
                    users.id AS id
                FROM team_members
                JOIN users ON team_members.user_id = users.id
                WHERE team_members.team_id = $teamID
            ";

            $builder = $this->db->query($sql);

            $result = $builder->getResultArray();

            return array_column($result, 'id');
            
        } else if ($select == 'ALL') {
            $sql = "
                SELECT 
                    team_members.id,
                    users.email,
                    users.picture
                FROM team_members
                JOIN users ON team_members.user_id = users.id
                WHERE team_members.team_id = $teamID
            ";

            $builder = $this->db->query($sql);

            return $builder->getResult();
        } else {
            $sql = "
                SELECT 
                    team_members.id,
                    team_members.user_id,
                    users.email,
                    users.picture
                FROM team_members
                JOIN users ON team_members.user_id = users.id
                WHERE team_members.team_id = $teamID
            ";

            $builder = $this->db->query($sql);

            return $builder->getResult();
        }
    }

    public function getTeamMemberUserIDByTeamID($teamID)
    {
        $sql = "
            SELECT 
                team_members.user_id
            FROM team_members
            WHERE team_members.team_id = $teamID
        ";

        $builder = $this->db->query($sql);

        return $builder->getResult();
    }

    public function deleteTeamMemberByTeamID($teamID)
    {
        $builder = $this->db->table('team_members');

        return $builder->where('team_id', $teamID)->delete();
    }

    public function getTeamMemberByUserID($userID)
    {
        $builder = $this->db->table('team_members');

        return $builder
            ->where('user_id', $userID)
            ->get()
            ->getResult();
    }

}
