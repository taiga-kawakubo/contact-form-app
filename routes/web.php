<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//管理画面を表示する

Route::middleware('auth')->group(function () {

    // 管理画面
    Route::get('/admin', fn() => '管理画面一覧（準備中）');


});
