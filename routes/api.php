<?php

use App\Http\Controllers\Api\V1\ContactController;
use Illuminate\Support\Facades\Route;

// 公開API(認証不要)
Route::prefix('v1')
    ->name('api.v1.')
    ->group(function () {
        Route::apiResource('contacts', ContactController::class);
    });
