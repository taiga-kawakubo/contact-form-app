<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| お問い合わせ画面
|--------------------------------------------------------------------------
*/

// お問い合わせフォームの表示
Route::get('/', [ContactController::class, 'index'])
    ->name('contacts.index');

// お問い合わせフォーム内容の確認
Route::post('/contacts/confirm', [ContactController::class, 'confirm'])
    ->name('contacts.confirm');

// お問い合わせフォーム内容の修正
Route::post('/contacts/back', [ContactController::class, 'back'])
    ->name('contacts.back');

// お問い合わせフォームの作成
Route::post('/contacts', [ContactController::class, 'store'])
    ->name('contacts.store');

// お問い合わせ完了画面
Route::get('/thanks', [ContactController::class, 'thanks'])
    ->name('contacts.thanks');

/*
|--------------------------------------------------------------------------
| お問い合わせ管理画面
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // 管理画面の表示
    Route::get('/admin', [AdminController::class, 'index'])
        ->name('admin.index');

    // お問い合わせ詳細画面の表示
    Route::get('/admin/contacts/{contact}', [AdminController::class, 'show'])
        ->name('admin.show');

    // お問い合わせの削除
    Route::delete('/admin/contacts/{contact}', [AdminController::class, 'destroy'])
        ->name('admin.delete');

    // タグの新規作成
    Route::post('/admin/tags', [TagController::class, 'store'])
        ->name('tags.store');

    // タグの編集画面に遷移
    Route::get('/admin/tags/{tag}/edit', [TagController::class, 'edit'])
        ->name('tags.edit');

    // タグの更新
    Route::put('/admin/tags/{tag}', [TagController::class, 'update'])
        ->name('tags.update');

    // タグの削除
    Route::delete('/admin/tags/{tag}', [TagController::class, 'destroy'])
        ->name('tags.delete');

    // CSVエクスポート
    Route::get('/contacts/export', [ContactController::class, 'export'])
        ->name('contacts.export');

});
