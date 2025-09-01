<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Admin\Auth\LoginController;

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

// 認証済みのユーザーからのリクエストのみ許可
Route::middleware(['auth:sanctum'])->group(function() {
    // ログインユーザー情報取得
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    // 一般ユーザーログアウト
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);

    // メール認証済みのユーザーからのリクエストのみ許可
    Route::middleware(['verified'])->group(function() {
        // 勤務状態確認
        Route::get('/attendance/state', [AttendanceController::class, 'state']);
        // 出勤
        Route::post('/attendance/work', [AttendanceController::class, 'work']);
        // 休憩開始
        Route::post('/attendance/break', [AttendanceController::class, 'break']);
        // 休憩終了
        Route::patch('/attendance/finish_break', [AttendanceController::class, 'finishBreak']);
        // 退勤
        Route::patch('/attendance/finish_work', [AttendanceController::class, 'finishWork']);
        // 勤怠一覧取得
        Route::get('/attendance/list', [AttendanceController::class, 'list']);
        // 勤怠詳細
        Route::get('/attendance/{id}', [AttendanceController::class, 'show']);
        // 勤怠修正
        Route::patch('/attendance/correction', [AttendanceController::class, 'correction']);
        // 勤怠新規作成
        Route::post('/attendance/create', [AttendanceController::class, 'create']);
        // 承認待ち一覧
        Route::get('/attendance/correction_request_list/waiting', [AttendanceController::class, 'waitingList']);
    });
});

// 管理者
Route::prefix('admin')->group(function () {
    // 管理者ログイン
    Route::post('/login', [LoginController::class, 'store']);

    // 認証済みかつ、管理者からのリクエストのみ許可
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        // ログインユーザー情報取得
        Route::get('/user', function (Request $request) {
            return $request->user('admin');
        });
        // 管理者ログアウト
        Route::post('/logout', [LoginController::class, 'destroy']);

        // メール認証済みの場合のみ、リクエスト許可
        Route::middleware(['verified'])->group(function() {
            
        });
    });
});


