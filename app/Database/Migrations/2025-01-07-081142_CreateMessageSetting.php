<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMessageSetting extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $sql = "CREATE TABLE `message_setting` (`id` INT NOT NULL AUTO_INCREMENT , `user_id` int  NULL , `message` TEXT NULL ,`created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP , `updated_at` DATETIME NULL DEFAULT NULL , `deleted_at` DATETIME NULL DEFAULT NULL , PRIMARY KEY (`id`))";
        $db->query($sql);
    }

    public function down()
    {
        //
    }
}
