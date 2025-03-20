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
  /**
   * The guard implementation.
   *
   * @var \Illuminate\Contracts\Auth\StatefulGuard
   */
  protected $guard;

  /**
   * Create a new controller instance.
   *
   * @param  \Illuminate\Contracts\Auth\StatefulGuard
   * @return void
   */
  public function __construct(StatefulGuard $guard)
  {
    $this->guard = $guard;
  }

  /**
   * Show the registration view.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Laravel\Fortify\Contracts\RegisterViewResponse
   */
  public function create(Request $request): RegisterViewResponse
  {
    return app(RegisterViewResponse::class);
  }

  /**
   * Create a new registered user.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Laravel\Fortify\Contracts\CreatesNewUsers  $creator
   * @return \Laravel\Fortify\Contracts\RegisterResponse
   */
  public function store(
    Request $request,
    CreatesNewUsers $creator
  ): RegisterResponse {
    event(new Registered($user = $creator->create($request->all())));

    // 登録後自動でログイン
    $this->guard->login($user);

    return app(RegisterResponse::class);
  }
}
