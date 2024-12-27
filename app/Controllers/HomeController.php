<?php

namespace App\Controllers;

class HomeController extends BaseController
{
    public function index()
    {
        $data['content'] = 'home/index';
        $data['title'] = 'Home';
        $data['rooms'] = [];

        echo view('/app', $data);
    }

    public function policy()
    {
        echo view('/policy');
    }
}
