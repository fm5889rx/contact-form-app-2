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

/**
 * ルーティング
 */
//Route::middleware('auth') function () {
    Route::get('/', [ContactController::class, 'index']);
    Route::post('/contacts/confirm', [ContactController::class, 'confirm']);
    Route::post('/contacts', [ContactController::class, 'thanks']);
// Route::get('/admin', AdminController::class, 'index']);
// Route::get('/admin/contscts/{conntact}', AdminController::class, 'show'])
// Route::get('/admin/tags/{tag}/edit', [TagController::class, 'edit']);
//};