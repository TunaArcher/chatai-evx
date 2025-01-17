<?php

namespace App\Libraries;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Checkout\Session;
use Stripe\BillingPortal\Session as BillingPortalSession;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));
    }

    public function createCustomer($name, $email)
    {
        return Customer::create([
            'name' => $name,
            'email' => $email,
        ]);
    }

    public function createCheckoutSession($customerId, $priceId, $successUrl, $cancelUrl, $metadata)
    {
        return Session::create([
            'customer' => $customerId,
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => $metadata,
        ]);
    }

    public function createBillingPortalSession($customerId)
    {
        return BillingPortalSession::create([
            'customer' => $customerId,
            'return_url' => base_url('/profile'),
        ]);
    }
}
