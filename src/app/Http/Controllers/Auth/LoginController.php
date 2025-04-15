<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{

    public function userLogin()
    {
        if (Auth::guard('web')->check()) {
            return redirect(route('user.dashboard'));
        }
        return view('auth.login');
    }

    public function adminLogin()
    {
        if (Auth::guard('admin')->check()) {
            return redirect(route('admin.dashboard'));
        }
        return view('auth.admin-login');
    }

    // ログイン状態で、かつブラウザで直接URLを入力した場合のルート変更
    public function rootCourseChange()
    {
        if (Auth::guard('admin')->check()) {
            return redirect(route('admin.dashboard'));
        }
        if (Auth::guard('web')->check()) {

            return redirect(route('user.dashboard'));
        }
        return redirect('/login');
    }
}
