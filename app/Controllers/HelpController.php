<?php

namespace App\Controllers;

class HelpController extends BaseController
{
    public function index()
    {
        $data['content'] = 'help/index';
        $data['title'] = 'Help';
        $data['rooms'] = [];

        echo view('/app', $data);
    }
}
