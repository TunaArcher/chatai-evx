<?php

namespace App\Controllers;

class PaymentController extends BaseController
{
    public function success()
    {
        echo view('/payment/success');
    }

    public function cancel()
    {
        echo view('/payment/cancel');
    }
}
