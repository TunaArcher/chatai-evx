<?php

namespace App\Models;

use CodeIgniter\Database\ConnectionInterface;

class PermissionModel
{

    protected $db;

    public function __construct()
    {
        $db = \Config\Database::connect();
        $this->db = &$db;
    }

    public function getPermissionAll()
    {
        $builder = $this->db->table('permissions');

        return $builder
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResult();
    }

    public function getPermissionByID($id)
    {
        $builder = $this->db->table('permissions');

        return $builder->where('id', $id)->get()->getRow();
    }

    public function insertPermission($data)
    {
        $builder = $this->db->table('permissions');

        return $builder->insert($data) ? $this->db->insertID() : false;
    }

    public function updatePermissionByID($id, $data)
    {
        $builder = $this->db->table('permissions');

        return $builder->where('id', $id)->update($data);
    }

    public function deletePermissionByID($id)
    {
        $builder = $this->db->table('permissions');

        return $builder->where('id', $id)->delete();
    }

    public function getPermissionByMenu($menu)
    {
        $builder = $this->db->table('permissions');

        return $builder
            ->where('menu_access', $menu)
            ->get()
            ->getRow();
    }
}
