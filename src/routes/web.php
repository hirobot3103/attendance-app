<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceListController;
use App\Http\Controllers\AttendanceAdminListController;
use App\Http\Controllers\AttendanceDetailController;
use App\Http\Controllers\RequestStampController;
use App\Http\Controllers\StaffListController;

// ルートの場合
// ログインの状況に応じて表示先を切り替える
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

// 申請関係
//一般ユーザー、管理者側とも同じURLのため、コントローラー内で処理を分岐
Route::middleware('verified')->group(function () {
    Route::get('/stamp_correction_request/list', [RequestStampController::class, 'index'])->name('attendant-req');
    Route::get('/stamp_correction_request/list/{pageId}', [RequestStampController::class, 'reqindex'])->name('attendant-reqindex');
    Route::get('/stamp_correction_request/{id}', [RequestStampController::class, 'detail'])->name('attendant-detail');
    Route::post('/stamp_correction_request/approve/{attendance_correct_request}', [RequestStampController::class, 'approve'])->name('attendant-approve');
});

// 一般ユーザーのログイン
Route::get('/login', function () {
    if (Auth::guard('web')->check()) {
        return redirect(route('user.dashboard'));
    }
    return view('auth.login');
})->name('login');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('guest:web');

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
    });
});

// 一般ユーザーのログアウト
Route::post('/logout', [LogoutController::class, 'userLogout'])->name('logout');

// 管理者のログイン
Route::get('/admin/login', function () {
    if (Auth::guard('admin')->check()) {
        return redirect(route('admin.dashboard'));
    }
    return view('auth.admin-login');
})->name('admin.login');

Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])->middleware('guest:admin');

Route::middleware(['admin.guard'])->group(function () {

    Route::get('/admin/attendance/list', [AttendanceAdminListController::class, 'index'])->name('admin.dashboard');
    Route::post('/admin/attendance/list', [AttendanceAdminListController::class, 'search'])->name('admin.attendant-serch');


    Route::get('/admin/staff/list', [StaffListController::class, 'index'])->name('admin.stafflist');
    Route::get('/admin/attendance/staff/{id}', [StaffListController::class, 'list'])->name('admin.staffattend');
    Route::post('/admin/attendance/staff/{id}', [StaffListController::class, 'search'])->name('admin.staffserach');
    Route::post('/admin/attendance/staff/detail/{id}', [StaffListController::class, 'detail'])->name('admin.staffdetail');
});

// 管理者のログアウト
Route::post('/admin/logout', [LogoutController::class, 'adminLogout'])->name('admin.logout');
