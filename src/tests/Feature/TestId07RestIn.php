<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Rest;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class TestId07RestIn extends TestCase
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
    public function 休憩機能_休憩ボタンが正しく機能する()
    {
        // 出勤中となっているデータを作成
        $params = [
            'user_id'  => $this->user->id,
            'clock_in' => now(),
            'status'   => 1,
        ];
        Attendance::create($params);

        $respose = $this->get('/login');

        $this->actingAs($this->user);

        $respose = $this->get('/attendance');
        $respose->assertSee('<button type="submit" class="rest-btn" name="rest_in">休憩入</button>', false);

        $respose = $this->post(
            '/attendance',
            [
                'rest_in' => null,
            ]
        );
        $respose = $this->get('/attendance');
        $respose->assertSee('休憩中', false);
    }

    /** @test */
    public function 休憩機能_休憩は一日に何回でもできる()
    {
        // 出勤中となっているデータを作成
        $params = [
            'user_id'  => $this->user->id,
            'clock_in' => now(),
            'status'   => 1,
        ];
        Attendance::create($params);

        $respose = $this->get('/login');
        $this->actingAs($this->user);

        $respose = $this->get('/attendance');
        $respose = $this->post(
            '/attendance',
            [
                'rest_in' => null,
            ]
        );

        $respose = $this->get('/attendance');
        $respose = $this->post(
            '/attendance',
            [
                'rest_out' => null,
            ]
        );

        $respose = $this->get('/attendance');
        $respose->assertSee('<button type="submit" class="rest-btn" name="rest_in">休憩入</button>', false);
    }

    /** @test */
    public function 休憩機能_休憩時刻が管理画面で確認できる()
    {
        // 出勤中となっているデータを作成
        $params = [
            'user_id'  => $this->user->id,
            'clock_in' => now(),
            'status'   => 1,
        ];
        Attendance::create($params);

        $respose = $this->get('/login');
        $this->actingAs($this->user);

        // 休憩入ボタン押下
        $respose = $this->get('/attendance');
        $respose = $this->post(
            '/attendance',
            [
                'rest_in' => null,
            ]
        );

        // 休憩戻ボタン押下
        $respose = $this->get('/attendance');
        $respose = $this->post(
            '/attendance',
            [
                'rest_out' => null,
            ]
        );
        $respose = $this->get('/attendance');
        $respose = $this->post('/logout');

        // 登録された出勤時間データに紐づいた休憩時間を取得する
        $clockInDate = Attendance::where('user_id', 6)->first();
        $restInDate = Rest::where('attendance_id', $clockInDate->id)->first();

        $respose = $this->get('/admin/login');
        $admin = Admin::where('id', 1)->first();
        $this->actingAs($admin, 'admin');

        //　ユーザー別勤怠データページを取得
        $tid = new Carbon($clockInDate->clock_in);
        $restInTime = new Carbon($restInDate->rest_in);
        $restOutTime = new Carbon($restInDate->rest_out);

        $respose = $this->post(route('admin.staffdetail', ['id' => $clockInDate->id]), [
            'tid' => $tid->format('Y-m-d'),
            'uid' => $clockInDate->user_id,
        ]);
        $respose->assertSee('value="' . $restInTime->format('H:i') . '"', false);
        $respose->assertSee('value="' . $restOutTime->format('H:i') . '"', false);
    }
}
