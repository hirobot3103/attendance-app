<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\AdminLoginRequest;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\LoginResponse;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;
use Illuminate\Http\Request;

// Fortifyのコントローラーをカスタム
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

        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                Fortify::username() => [__('auth.failed')],
            ]);
        }

        $request->session()->regenerate();

        return app(LoginResponse::class);
    }
}
