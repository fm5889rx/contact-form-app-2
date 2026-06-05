<?php

use App\Http\Controllers\Api\ContactController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// 公開API（認証不要）
Route::get('/v1/contacts', [ContactController::class, 'index']);
Route::get('/v1/contacts/{contact}', [ContactController::class, 'show']);
Route::post('/v1/contacts', [ContactController::class, 'store']);
Route::put('/v1/contacts/{contact}', [ContactController::class, 'update']);
Route::delete('/v1/contacts/{contact}', [ContactController::class, 'destroy']);
