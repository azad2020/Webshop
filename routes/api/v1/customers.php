<?php


use Illuminate\Support\Facades\Route;


Route::middleware([
    /*'auth',*/
])
    ->name('customers.')
    ->group(function () {

        Route::get('/customers', [\App\Http\Controllers\CustomerController::class, 'index'])
            ->name('index')
            ->withoutMiddleware('auth');

        Route::get('/customers/{id}', [\App\Http\Controllers\CustomerController::class, 'show'])
            ->name('show')
            ->whereNumber('customer');

        Route::post('/customers', [\App\Http\Controllers\CustomerController::class, 'store'])->name('store');

        Route::patch('/customers/{id}', [\App\Http\Controllers\CustomerController::class, 'update'])->name('update');

        Route::delete('/customers/{id}', [\App\Http\Controllers\CustomerController::class, 'destroy'])->name('destroy');
    });
