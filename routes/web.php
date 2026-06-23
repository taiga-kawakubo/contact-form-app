<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContactController;
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

//お問い合わせフォームの表示
Route::get('/',[ContactController::class,'index']);

//お問い合わせフォーム内容の確認
Route::post('/contacts/confirm',[ContactController::class,'confirm']);

//お問い合わせフォームの作成
Route::post('/contacts', [ContactController::class, 'store']);

//お問い合わせ完了画面
Route::get('/contacts/thanks', [ContactController::class, 'thanks'])
    ->name('contact.thanks');





//管理画面

Route::middleware('auth')->group(function () {

    // 管理画面を表示する
    Route::get('/admin', fn() => '管理画面一覧（準備中）');


});
