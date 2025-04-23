<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\User;


use Tests\TestCase;
use Carbon\Carbon;

class TestId04GetDate extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setup(): void
    {
        parent::setup();

        Artisan::call('migrate:refresh', ['--env' => 'testing']);
        $this->seed();
        $this->user = User::create(
            [
                'name' => 'testman',
                'email' => 'testman@attendance.com',
                'password' => 'passwordtest',
                'email_verified_at' => now(),
            ]
        );
    }

    /** @test */
    public function 日時取得機能_現在の日時情報がUIと同じ形式で出力されている()
    {

        // ログイン画面を開く
        $response = $this->get('/login');
        $this->actingAs($this->user);

        //  現時点と比較する
        $response = $this->get('/attendance');
        $response->assertViewIs('top');
        $serverTime = new Carbon();
        $searchString = '<input type="hidden" value="' . $serverTime->format('Y-m-d h:i') . '">';
        $response->assertSee($searchString, false);
    }
}
