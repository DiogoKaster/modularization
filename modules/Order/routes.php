<?php

use Illuminate\Support\Facades\Route;
use Modules\Order\Http\Controller\CheckoutController;

Route::middleware('auth')->group(function () {
    Route::post('checkout', CheckoutController::class)->name('checkout');
});
