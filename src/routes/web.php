<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\VerificationController;

// 一般ユーザーのログイン
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest:web');

Route::middleware(['auth:web'])->group(function () {

    // メール認証誘導画面関連
    Route::get('/email/verify', [VerificationController::class, 'notice'])->name('verification.notice');

    Route::post('/email/resend', [VerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.resend');

    // メール認証済みが必須のルート
    Route::middleware('verified')->group(function () {

        Route::get('/attendance', function () {
            return view('top');
        })->name('user.dashboard');

        Route::get('/attendance/list', function () {
            return view('attendance-list');
        })->name('user.attendant-list');
    });
});

// 管理者のログイン
Route::get('/admin/login', function () {
    return view('auth.admin-login');
})->name('admin.login');

Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest:admin');

Route::middleware(['admin.guard'])->group(function () {
    Route::get('/admin/attendance/list', function () {
        return view('attendance-admin-list');
    })->name('admin.dashboard');
});

// ログアウト
// Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
// 一般ユーザーのログアウト
Route::post('/logout', [LogoutController::class, 'userLogout'])->name('logout');

// 管理者のログアウト
Route::post('/admin/logout', [LogoutController::class, 'adminLogout'])->name('admin.logout');
// Route::get('/', function () {
//     return view('welcome');
// });
