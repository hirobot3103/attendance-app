<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceListController;
use App\Http\Controllers\AttendanceDetailController;
use App\Http\Controllers\RequestStampController;
use App\Http\Controllers\RequestStampAdminController;
use App\Http\Controllers\StaffListController;

// ルートの場合
Route::get('/', function () {
    $previousUrl = url()->previous();
    if (preg_match("/\wadmin/", $previousUrl, $result)) {
        if (Auth::guard('admin')->check()) {
            return redirect($previousUrl);
        }
        return redirect('/admin/login');
    }
    if (Auth::guard('web')->check()) {
        return redirect($previousUrl);
    }
    return redirect('/login');
});

// 一般ユーザーのログイン
Route::get('/login', function () {
    if (Auth::guard('web')->check()) {
        return redirect(route('user.dashboard'));
    }
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
    if (Auth::guard('admin')->check()) {
        return view('attendance-admin-list');
    }
    return view('auth.admin-login');
})->name('admin.login');

Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest:admin');

Route::middleware(['admin.guard'])->group(function () {

    Route::get('/admin/attendance/list', function () {
        return view('attendance-admin-list');
    })->name('admin.dashboard');

    Route::get('/admin/staff/list', [StaffListController::class, 'index'])->name('admin.stafflist');
    Route::get('/admin/attendance/staff/{id}', [StaffListController::class, 'list'])->name('admin.staffattend');
    Route::post('/admin/attendance/staff/{id}', [StaffListController::class, 'search'])->name('admin.staffserach');

    Route::get('/stamp_correction_request/list', [RequestStampAdminController::class, 'index'])->name('admin.attendant-req');
    Route::get('/stamp_correction_request/list/{pageId}', [RequestStampAdminController::class, 'reqindex'])->name('admin.attendant-reqindex');
    Route::get('/stamp_correction_request/{id}', [RequestStampAdminController::class, 'detail'])->name('admin.attendant-detail');
});

// ログアウト
// 一般ユーザーのログアウト
Route::post('/logout', [LogoutController::class, 'userLogout'])->name('logout');

// 管理者のログアウト
Route::post('/admin/logout', [LogoutController::class, 'adminLogout'])->name('admin.logout');
