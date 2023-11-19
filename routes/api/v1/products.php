<?php


use Illuminate\Support\Facades\Route;

Route::middleware([
    /*'auth',*/
])
    ->name('products.')
    ->group(function () {

        Route::get('/products', [\App\Http\Controllers\ProductController::class, 'index'])
            ->name('index')
            ->withoutMiddleware('auth');

        Route::get('/products/{product}', [\App\Http\Controllers\ProductController::class, 'show'])
            ->name('show')
            ->whereNumber('post');

        Route::post('/products', [\App\Http\Controllers\ProductController::class, 'store'])->name('store');

        Route::patch('/products/{id}', [\App\Http\Controllers\ProductController::class, 'update'])->name('update');

        Route::delete('/products/{id}', [\App\Http\Controllers\ProductController::class, 'destroy'])->name('destroy');
    });
