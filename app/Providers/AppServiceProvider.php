<?php

namespace App\Providers;

use App\Interfaces\PaymentProviderInterface;
use App\PaymentProviders\SuperPaymentProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(PaymentProviderInterface::class, SuperPaymentProvider::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
