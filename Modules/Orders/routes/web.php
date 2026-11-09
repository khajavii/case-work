<?php

use Illuminate\Support\Facades\Route;
use Modules\Orders\Http\Controllers\OrderController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('orders', OrderController::class)->names('orders');
});
