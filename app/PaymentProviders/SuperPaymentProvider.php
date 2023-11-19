<?php

namespace App\PaymentProviders;

use App\Interfaces\PaymentProviderInterface;
use Illuminate\Support\Facades\Http;


class SuperPaymentProvider implements PaymentProviderInterface
{
    public function processPayment($paymentData)
    {
        // Implement the logic for SuperPaymentProvider
        $paymentResponse = Http::post('https://superpay.view.agentur-loop.com/pay', $paymentData);

        return $paymentResponse;

    }
}
