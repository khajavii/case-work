<?php

use Illuminate\Support\Facades\Route;
use Modules\Production\Http\Controllers\Api\V1\ProductionController;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    Route::apiResource('productions', ProductionController::class)
        ->only('index', 'store', 'show')
        ->names('production');

    Route::patch(
        'jobs/{jobId}/status',
        [ProductionController::class, 'changeStatus']
    )
        ->whereNumber('jobId')
        ->name('jobs.change-status');
});
