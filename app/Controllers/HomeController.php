<?php

namespace App\Controllers;

class HomeController extends BaseController
{
    public function index()
    {
        $data['content'] = 'home/index';
        $data['title'] = 'Home';

        echo view('/app', $data);
    }
}
