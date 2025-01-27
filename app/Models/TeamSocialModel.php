<?php

namespace App\Models;

use CodeIgniter\Database\ConnectionInterface;

class TeamSocialModel
{

    protected $db;

    public function __construct()
    {
        $db = \Config\Database::connect();
        $this->db = &$db;
    }

    public function getTeamSocialAll()
    {
        $builder = $this->db->table('team_socials');

        return $builder
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResult();
    }

    public function getTeamSocialByID($id)
    {
        $builder = $this->db->table('team_socials');
        return $builder->where('id', $id)->get()->getRow();
    }

    public function getMessageTraningByID($id)
    {
        $builder = $this->db->table('message_setting');
        return $builder->where('TeamSocial_id', $id)->get()->getRow();
    }

    public function insertTeamSocial($data)
    {
        $builder = $this->db->table('team_socials');

        return $builder->insert($data) ? $this->db->insertID() : false;
    }

    public function updateTeamSocialByID($id, $data)
    {
        $builder = $this->db->table('team_socials');

        return $builder->where('id', $id)->update($data);
    }

    public function deleteTeamSocialByID($id)
    {
        $builder = $this->db->table('team_socials');

        return $builder->where('id', $id)->delete();
    }

    public function getTeamSocial($TeamSocialname)
    {
        $builder = $this->db->table('team_socials');
        return $builder->where('TeamSocialname', $TeamSocialname)->get()->getResult();
    }

    public function getTeamSocialByPlatFromAndID($platform, $platformTeamSocialID)
    {
        $builder = $this->db->table('team_socials');

        return $builder
            ->where('sign_by_platform', $platform)
            ->where('platform_TeamSocial_id', $platformTeamSocialID)
            ->get()
            ->getRow();
    }

    public function getTeamSocialByEmail($email)
    {
        $builder = $this->db->table('team_socials');

        return $builder->where('email', $email)->get()->getRow();
    }

    public function getTeamSocialByUserSocialID($userSocialID)
    {
        $builder = $this->db->table('team_socials');

        return $builder->where('social_id', $userSocialID)->get()->getResult();
    }

    public function getTeamSocialByTeamID($teamID, $select = 'ALL')
    {

        if ($select == 'ONLY_ID') {
            $sql = "
                SELECT 
                    user_socials.id AS id,
                    team_socials.social_id AS user_social_id
                FROM team_socials
                JOIN user_socials ON user_socials.id = team_socials.social_id
                WHERE team_socials.team_id = $teamID
            ";

            $builder = $this->db->query($sql);

            $result = $builder->getResultArray();

            return array_column($result, 'id');
        } else if ($select == 'ALL') {
            $sql = "
                SELECT 
                    user_socials.src,
                    team_socials.id,
                    user_socials.platform,
                    user_socials.name,
                    team_socials.social_id AS user_social_id
                FROM team_socials
                JOIN user_socials ON user_socials.id = team_socials.social_id
                WHERE team_socials.team_id = $teamID
            ";

            $builder = $this->db->query($sql);

            return $builder->getResult();
        }
    }
 
    public function deleteTeamSocialByTeamID($teamID)
    {
        $builder = $this->db->table('team_socials');

        return $builder->where('team_id', $teamID)->delete();
    }
}
