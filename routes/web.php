<?php

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
/**
 * Controllerクラスの使用宣言
 */
use App\Http\Controllers\ContactController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\AuthController;

/**
 * ルーティング
 */
Route::get('/', [ContactController::class, 'index'])->name('contacts.index');
Route::get('/contacts/confirm', [ContactController::class, 'confirmView'])->name('contacts.confirm');
Route::post('/contacts/confirm', [ContactController::class, 'confirm'])->name('contacts.confirm.submit');
Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
Route::get('/thanks', [ContactController::class, 'thanks']);
Route::get('/contacts/export', [ContactController::class, 'export'])->name('contacts.export');

Route::middleware('auth')->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/contacts/{contact}', [AdminController::class, 'show'])->name('admin.show');
    Route::delete('/admin/contacts/{contact}', [AdminController::class, 'destroy']);

    Route::post('/admin/tags', [TagController::class, 'store']);
    Route::get('/admin/tags/{tag}/edit', [TagController::class, 'edit'])->name('admin.tags.edit');
    Route::put('/admin/tags/{tag}', [TagController::class, 'update']);
    Route::delete('admin/tags/{tag}', [TagController::class, 'destroy']);
});

Route::post('/login', [AuthController::class, 'login'])->name('login')->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/register', [AuthController::class, 'register'])->name('register')->middleware('guest');
