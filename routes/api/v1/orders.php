<?php


use Illuminate\Support\Facades\Route;

// we can also use apiResource for more convenience
//Route::apiResource('orders', \App\Http\Controllers\OrderController::class);

// I used this approach for more readability, maintainability and flexibility
Route::middleware([
    /*'auth',*/
])
    ->name('orders.')
    ->group(function () {

        Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index'])
            ->name('index')
            ->withoutMiddleware('auth');

        Route::get('/orders/{id}', [\App\Http\Controllers\OrderController::class, 'show'])
            ->name('show')
            ->whereNumber('order');

        Route::post('/orders', [\App\Http\Controllers\OrderController::class, 'store'])->name('store');

        Route::patch('/orders/{id}', [\App\Http\Controllers\OrderController::class, 'update'])->name('update');

        Route::delete('/orders/{id}', [\App\Http\Controllers\OrderController::class, 'destroy'])->name('destroy');

        // Adding a product to an order
        Route::post('/orders/{order}/add-product', [\App\Http\Controllers\OrderController::class, 'addProduct'])
            ->name('addProduct')
            ->withoutMiddleware('auth');

        Route::post('/orders/{id}/pay', [\App\Http\Controllers\OrderController::class, 'payOrder'])
            ->name('payOrder');

    });
