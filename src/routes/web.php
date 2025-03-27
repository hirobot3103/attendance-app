<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceListController;
use App\Http\Controllers\AttendanceDetailController;
use App\Http\Controllers\RequestStampController;

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

        Route::get('/attendance', [AttendanceController::class, 'index'])->name('user.dashboard');
        Route::post('/attendance', [AttendanceController::class, 'action'])->name('user.actions');

        Route::get('/attendance/list', [AttendanceListController::class, 'index'])->name('user.attendant-list');
        Route::post('/attendance/list', [AttendanceListController::class, 'search'])->name('user.attendant-serch');
        Route::get('/attendance/{id}', [AttendanceDetailController::class, 'detail'])->name('user.attendant-detail');
        Route::post('/attendance/{id}', [AttendanceDetailController::class, 'modify'])->name('user.attendant-mod');

        Route::get('/stamp_correction_request/list', [RequestStampController::class, 'index'])->name('user.attendant-req');
        Route::get('/stamp_correction_request/list/{pageId}', [RequestStampController::class, 'reqindex'])->name('user.attendant-reqindex');
        Route::get('/stamp_correction_request/{id}', [RequestStampController::class, 'detail'])->name('user.attendant-detail');
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
// 一般ユーザーのログアウト
Route::post('/logout', [LogoutController::class, 'userLogout'])->name('logout');

// 管理者のログアウト
Route::post('/admin/logout', [LogoutController::class, 'adminLogout'])->name('admin.logout');
