<?php

namespace App\Controllers\cronjob;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Reset extends BaseController
{
    public function run()
    {
        $this->reset_free_request_limit();
    }

    private function reset_free_request_limit()
    {
        $userModel = new UserModel();
        try {
            $userModel->updateUser(['free_request_limit' => 0]);
        } catch (\Exception $e) {
            log_message('error', "Reset::reset_free_request_limit error: " . $e->getMessage());
        }
    }
}
