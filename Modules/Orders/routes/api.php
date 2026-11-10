<?php

use Illuminate\Support\Facades\Route;
use Modules\Orders\Http\Controllers\Api\V1\OrderController;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    Route::apiResource('orders', OrderController::class)
        ->only(['index', 'store'])
        ->names('orders');
});
