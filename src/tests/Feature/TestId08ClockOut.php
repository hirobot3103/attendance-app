<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class TestId08ClockOut extends TestCase
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
    public function 退勤機能_退勤ボタンが正しく機能する()
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
        $respose->assertSee('<button type="submit" class="attendance-btn" name="clock_out">退&nbsp;勤</button>', false);

        $respose = $this->post(
            '/attendance',
            [
                'clock_out' => null,
            ]
        );
        $respose = $this->get('/attendance');
        $respose->assertSee('<div class="attendance-status">退勤済</div>', false);
    }

    /** @test */
    public function 退勤機能_退勤時刻が管理画面で確認できる()
    {
        $respose = $this->get('/login');
        $this->actingAs($this->user);

        // 出勤ボタン押下
        $respose = $this->get('/attendance');
        $respose = $this->post(
            '/attendance',
            [
                'clock_in' => null,
            ]
        );

        // 退勤ボタン押下
        $respose = $this->get('/attendance');
        $respose = $this->post(
            '/attendance',
            [
                'clock_out' => null,
            ]
        );
        $respose = $this->get('/attendance');
        $respose = $this->post('/logout');

        // 登録された出勤時間データに紐づいた休憩時間を取得する
        $clockInDate = Attendance::where('user_id', 6)->first();

        $respose = $this->get('/admin/login');
        $admin = Admin::where('id', 1)->first();
        $this->actingAs($admin, 'admin');

        //　ユーザー別勤怠データページを取得
        $tid = new Carbon($clockInDate->clock_in);
        $clockInTime = new Carbon($clockInDate->clock_in);
        $clockOutTime = new Carbon($clockInDate->clock_out);

        $respose = $this->post(route('admin.staffdetail', ['id' => $clockInDate->id]), [
            'tid' => $tid->format('Y-m-d'),
            'uid' => $clockInDate->user_id,
        ]);
        $respose->assertSee('value="' . $clockInTime->format('H:i') . '"', false);
        $respose->assertSee('value="' . $clockOutTime->format('H:i') . '"', false);
    }
}
