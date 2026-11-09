<?php

use Illuminate\Support\Facades\Route;
use Modules\Production\Http\Controllers\ProductionController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('productions', ProductionController::class)->names('production');
});
