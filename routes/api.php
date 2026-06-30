<?php

use App\Http\Controllers\Api\v1\ContactController;
use Illuminate\Support\Facades\Route;

Route::apiResource('v1/contacts', ContactController::class);
