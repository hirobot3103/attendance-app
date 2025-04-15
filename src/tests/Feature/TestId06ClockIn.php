<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class TestId06ClockIn extends TestCase
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
    public function 出勤機能_出勤機能が正しく機能する()
    {
        $respose = $this->get('/login');

        $this->actingAs($this->user);

        $respose = $this->get('/attendance');
        $respose->assertSee('<button type="submit" class="attendance-btn" name="clock_in">出&nbsp;勤</button>', false);

        $respose = $this->post(
            '/attendance',
            [
                'clock_in' => null,
            ]
        );
        $respose = $this->get('/attendance');
        $respose->assertSee('出勤中', false);
    }

    /** @test */
    public function 出勤機能_出勤は一日一回のみできる()
    {
        $respose = $this->get('/login');

        $this->actingAs($this->user);

        $respose = $this->get('/attendance');
        $respose->assertSee('<button type="submit" class="attendance-btn" name="clock_in">出&nbsp;勤</button>', false);
        $respose = $this->post(
            '/attendance',
            [
                'clock_in' => null,
            ]
        );

        $respose = $this->get('/attendance');
        $respose = $this->post(
            '/attendance',
            [
                'clock_out' => null,
            ]
        );

        $respose = $this->get('/attendance');
        $respose->assertDontSee('<button type="submit" class="attendance-btn" name="clock_in">出&nbsp;勤</button>', false);
    }

    /** @test */
    public function 出勤機能_出勤時刻が管理画面で確認できる()
    {
        $respose = $this->get('/login');

        $this->actingAs($this->user);

        $respose = $this->get('/attendance');
        $respose->assertSee('<button type="submit" class="attendance-btn" name="clock_in">出&nbsp;勤</button>', false);
        $respose = $this->post(
            '/attendance',
            [
                'clock_in' => null,
            ]
        );
        $this->post('/logout');

        // 登録された出勤時間データを取得する
        $clockInDate = Attendance::where('user_id', 6)->first();

        $respose = $this->get('/admin/login');
        $admin = Admin::where('id', 1)->first();
        $this->actingAs($admin, 'admin');

        //　ユーザー別勤怠データページを取得
        $respose = $this->post(route('admin.staffdetail', ['id' => $clockInDate->id]));
        dd($respose);


        // $this->actingAs($this->user);


        // $respose = $this->get('/attendance');
        // $respose = $this->post(
        //     '/attendance',
        //     [
        //         'clock_out' => null,
        //     ]
        // );

        // $respose = $this->get('/attendance');
        // $respose->assertDontSee('<button type="submit" class="attendance-btn" name="clock_in">出&nbsp;勤</button>', false);
    }
}
