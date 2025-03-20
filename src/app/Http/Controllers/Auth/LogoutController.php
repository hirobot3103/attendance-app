<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class LogoutController extends Controller
{
    public function userLogout(Request $request)
    {
        Auth::guard('web')->logout();
        return redirect('/login'); // 一般ユーザー用
    }

    public function adminLogout(Request $request)
    {
        Auth::guard('admin')->logout();
        return redirect('/admin/login'); // 管理者用
    }
}
