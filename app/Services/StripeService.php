<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\PaymentIntent;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('stripe.stripe_sk'));
    }

    public function createPaymentIntent($amount)
    {
        return PaymentIntent::create([
            'amount' => $amount * 100, // convertir a centavos
            'currency' => 'usd',
        ]);
    }
}
