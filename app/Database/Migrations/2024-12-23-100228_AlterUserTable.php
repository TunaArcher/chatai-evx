<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterUserTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $db->query("
            ALTER TABLE `chatai-oauth`.`users` 
            ADD COLUMN `sign_by_platform` VARCHAR(45) NULL DEFAULT NULL AFTER `id`,
            ADD COLUMN `platform_user_id` VARCHAR(200) NULL DEFAULT NULL AFTER `sign_by_platform`,
            ADD COLUMN `access_token_meta` TEXT(200) NULL DEFAULT NULL AFTER `deleted_at`,
            ADD COLUMN `name` VARCHAR(45) NULL DEFAULT NULL AFTER `access_token_meta`,
            ADD COLUMN `picture` TEXT(200) NULL DEFAULT NULL AFTER `name`,
            ADD COLUMN `email` VARCHAR(45) NULL DEFAULT NULL AFTER `picture`,
            ADD COLUMN `access_token_instagram` TEXT(200) NULL DEFAULT NULL AFTER `email`,
            ADD COLUMN `access_token_whatsapp` TEXT(200) NULL DEFAULT NULL AFTER `access_token_instagram`;
        ");
    }

    public function down()
    {
        //
    }
}
