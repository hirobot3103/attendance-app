<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\AdminLoginRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthCustomAuthenticatedSessionController extends Controller
{
    public function store(Request $request)
    {
        $input = [
            'email' => $request['email'],
            'password' => $request['password'],
        ];

        if ($request->is('admin/*')) {
            $RequestInstance = new AdminLoginRequest();
        } else {
            $RequestInstance = new LoginRequest();
        }
        $credentials = Validator::make(
            $input,
            $RequestInstance->rules(),
            $RequestInstance->messages(),
        )->validate();

        if ($request->is('admin/*')) {
            $admin = Admin::where('email', $credentials['email'])->first();
            if ($admin && Hash::check($credentials['password'], $admin->password)) {
                Auth::guard('admin')->login($admin);
                return redirect(route('admin.dashboard'));
            }
        } else {
            $user = User::where('email', $credentials['email'])->first();
            if ($user && Hash::check($credentials['password'], $user->password)) {
                Auth::guard('web')->login($user);
                return redirect(route('user.dashboard'));
            }
        }

        return null;
    }
}
