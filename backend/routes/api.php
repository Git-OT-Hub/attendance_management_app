<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\TestController;

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

// 一般ユーザー登録
Route::post('/register', [RegisteredUserController::class, 'store']);
// 一般ユーザーログイン
Route::post('/login', [AuthenticatedSessionController::class, 'store']);

// 認証済みのリクエストのみ許可
Route::middleware(['auth:sanctum'])->group(function() {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // 一般ユーザーログアウト
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
});

Route::get('/test', [TestController::class, 'index']);
