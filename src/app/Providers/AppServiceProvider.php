<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\VerifyEmailViewResponse;
use App\Http\Responses\CustomVerifyEmailViewResponse;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->app->singleton(VerifyEmailViewResponse::class, CustomVerifyEmailViewResponse::class);
    }
}
