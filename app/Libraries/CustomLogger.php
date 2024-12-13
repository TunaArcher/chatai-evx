<?php

namespace App\Libraries;

use CodeIgniter\Log\Handlers\FileHandler;

class CustomLogger extends FileHandler
{
    public function handle($level, $message): bool
    {
        // กรองข้อความที่ไม่ต้องการบันทึก
        if (strpos($message, "Session: Class initialized using 'CodeIgniter\Session\Handlers\FileHandler' driver.") !== false) {
            return false; // ไม่บันทึกข้อความนี้
        }

        // บันทึกข้อความอื่น ๆ
        return parent::handle($level, $message);
    }
}
