<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TagController;
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

// お問い合わせフォームの表示
Route::get('/', [ContactController::class, 'index'])
    ->name('contact.index');

// お問い合わせフォーム内容の確認
Route::post('/contacts/confirm', [ContactController::class, 'confirm'])
    ->name('contact.confirm');

// お問い合わせフォームの作成
Route::post('/contacts', [ContactController::class, 'store'])
    ->name('contact.store');

// お問い合わせ完了画面
Route::get('/contacts/thanks', [ContactController::class, 'thanks'])
    ->name('contact.thanks');

// 管理画面

Route::middleware('auth')->group(function () {

    // 管理画面を表示する
    Route::get('/admin', [AdminController::class, 'index'])
        ->name('admin.index');

    // 詳細画面を表示する
    Route::get('/admin/contacts/{contact}', [AdminController::class, 'show']);

    // お問い合わせの削除
    Route::delete('/admin/contacts/{contact}', [AdminController::class, 'destroy'])
        ->name('admin.delete');

    // タグの新規作成
    Route::post('/admin/tags', [TagController::class, 'store'])
        ->name('tags.store');

    // タグの編集画面に遷移する
    Route::get('/admin/tags/{tag}/edit', [TagController::class, 'edit']);

    // タグの更新
    Route::put('/admin/tags/{tag}', [TagController::class, 'update'])
        ->name('tags.update');

    // タグの削除
    Route::delete('/admin/tags/{tag}', [TagController::class, 'destroy']);

    // CSVエクスポート
    Route::get('/contacts/export', [ContactController::class, 'export'])
        ->name('contacts,export');

});
