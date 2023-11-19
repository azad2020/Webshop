<?php

namespace App\Interfaces;

interface PaymentProviderInterface
{
    public function processPayment($paymentData);
}


