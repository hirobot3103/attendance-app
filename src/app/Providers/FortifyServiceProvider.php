<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Admin;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\AdminLoginRequest;

// // Fortifyでカスタムフォームリクエストを使うために必要
// use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;


use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\Auth\RegisterController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 登録成功後に自動ログインをしない指定をするために
        // FortifyのRegisterUserControllerをRegisterControllerに切り替え
        $this->app->singleton(
            RegisteredUserController::class,
            RegisterController::class,
        );

        $this->app->singleton(VerifyEmailResponseContract::class, function () {
            return new class implements VerifyEmailResponseContract {
                public function toResponse($request)
                {
                    return redirect('/attendance'); // 認証後のリダイレクト先
                }
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::loginView(function (Request $request) {
            return $request->is('admin/*') ? view('auth.admin-login') : view('auth.login');
        });

        Fortify::authenticateUsing(function (Request $request) {

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
                    return $admin;
                }
            } else {
                $user = User::where('email', $credentials['email'])->first();
                if ($user && Hash::check($credentials['password'], $user->password)) {
                    Auth::guard('web')->login($user);
                    return $user;
                }
            }

            return null;
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        Fortify::registerView(function () {
            return view('auth.reg');
        });

        Fortify::loginView(function () {
            return view('auth.login');
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });

        // $this->app->bind(FortifyLoginRequest::class, LoginRequest::class);
    }
}
