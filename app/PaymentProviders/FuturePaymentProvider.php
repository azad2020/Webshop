<?php

namespace App\PaymentProviders;

use App\Interfaces\PaymentProviderInterface;


class FuturePaymentProvider implements PaymentProviderInterface
{
    public function processPayment($paymentData)
    {
        // Logic for future Payment Provider
    }
}
