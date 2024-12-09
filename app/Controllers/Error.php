<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Errors extends BaseController
{
	public function show404()
	{
        echo view('/404');
	}

	public function showMaintenance() {}
}