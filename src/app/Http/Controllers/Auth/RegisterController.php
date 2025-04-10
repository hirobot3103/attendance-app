<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Contracts\RegisterViewResponse;

class RegisterController extends Controller
{
  protected $guard;

  public function __construct(StatefulGuard $guard)
  {
    $this->guard = $guard;
  }

  public function create(Request $request): RegisterViewResponse
  {
    return app(RegisterViewResponse::class);
  }

  public function store(Request $request, CreatesNewUsers $creator): RegisterResponse
  {

    event(new Registered($user = $creator->create($request->all())));

    // 登録後自動でログイン
    $this->guard->login($user);

    return app(RegisterResponse::class);
  }
}
