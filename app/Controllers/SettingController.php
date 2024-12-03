<?php

namespace App\Controllers;

class HomeController extends BaseController
{
    public function index()
    {
        $data['content'] = 'setting/index';
        $data['title'] = 'Setting';

        echo view('setting');
    }
}
