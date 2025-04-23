<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class TestId10AttendanceDetail extends TestCase
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
    public function 勤怠詳細情報取得機能（一般ユーザー）_勤怠詳細画面の「名前」がログインユーザーの氏名になっている()
    {
        // 勤怠データを作成
        $currentTime = new Carbon();
        $params = [
            'user_id'  => $this->user->id,
            'clock_in' => $currentTime->format('Y-m') . "-01 07:52:59",
            'clock_out' => $currentTime->format('Y-m') . "-01 17:00:00",
            'status'   => 2,
        ];

        Attendance::create($params);
        $clockInDate = Attendance::where('user_id', 6)->first();

        $paramsRest = [
            'attendance_id'  => $clockInDate->id,
            'rest_in' => $currentTime->format('Y-m') . "-01 10:55:59",
            'rest_out' => $currentTime->format('Y-m') . "-01 11:55:00",
        ];
        Rest::create($paramsRest);

        $respose = $this->get('/login');
        $this->actingAs($this->user);

        $respose = $this->get('/attendance/' . $clockInDate->id . "?tid=" . $currentTime->format('Y-m'));
        $respose->assertSee('<div class="name-section__content">testman</div>', false);
    }

    /** @test */
    public function 勤怠詳細情報取得機能（一般ユーザー）_勤怠詳細画面の「日付」が選択した日付になっている()
    {
        // 勤怠データを作成
        $currentTime = new Carbon();
        $params = [
            'user_id'  => $this->user->id,
            'clock_in' => $currentTime->format('Y-m') . "-01 07:52:59",
            'clock_out' => $currentTime->format('Y-m') . "-01 17:00:00",
            'status'   => 2,
        ];

        Attendance::create($params);
        $clockInDate = Attendance::where('user_id', 6)->first();

        $paramsRest = [
            'attendance_id'  => $clockInDate->id,
            'rest_in' => $currentTime->format('Y-m') . "-01 10:55:59",
            'rest_out' => $currentTime->format('Y-m') . "-01 11:55:00",
        ];
        Rest::create($paramsRest);

        $respose = $this->get('/login');
        $this->actingAs($this->user);

        $respose = $this->get('/attendance/' . $clockInDate->id . "?tid=" . $currentTime->format('Y-m'));
        $respose->assertSee('<div class="date-section__year">' . $currentTime->format('Y') . '年</div>', false);
        $respose->assertSee('<div class="date-section__month">' . $currentTime->format('m') . '月01日</div>', false);
    }

    /** @test */
    public function 勤怠詳細情報取得機能（一般ユーザー）_「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している()
    {
        // 勤怠データを作成
        $currentTime = new Carbon();
        $params = [
            'user_id'  => $this->user->id,
            'clock_in' => $currentTime->format('Y-m') . "-01 07:52:59",
            'clock_out' => $currentTime->format('Y-m') . "-01 17:00:00",
            'status'   => 2,
        ];

        Attendance::create($params);
        $clockInDate = Attendance::where('user_id', 6)->first();

        $paramsRest = [
            'attendance_id'  => $clockInDate->id,
            'rest_in' => $currentTime->format('Y-m') . "-01 10:55:59",
            'rest_out' => $currentTime->format('Y-m') . "-01 11:55:00",
        ];
        Rest::create($paramsRest);

        $respose = $this->get('/login');
        $this->actingAs($this->user);

        $respose = $this->get('/attendance/' . $clockInDate->id . "?tid=" . $currentTime->format('Y-m'));
        $respose->assertSee('value="07:52"', false);
        $respose->assertSee('value="17:00"', false);
    }

    /** @test */
    public function 勤怠詳細情報取得機能（一般ユーザー）_「休憩」にて記されている時間がログインユーザーの打刻と一致している()
    {
        // 勤怠データを作成
        $currentTime = new Carbon();
        $params = [
            'user_id'  => $this->user->id,
            'clock_in' => $currentTime->format('Y-m') . "-01 07:52:59",
            'clock_out' => $currentTime->format('Y-m') . "-01 17:00:00",
            'status'   => 2,
        ];

        Attendance::create($params);
        $clockInDate = Attendance::where('user_id', 6)->first();

        $paramsRest = [
            'attendance_id'  => $clockInDate->id,
            'rest_in' => $currentTime->format('Y-m') . "-01 10:55:59",
            'rest_out' => $currentTime->format('Y-m') . "-01 11:55:00",
        ];
        Rest::create($paramsRest);

        $respose = $this->get('/login');
        $this->actingAs($this->user);

        $respose = $this->get('/attendance/' . $clockInDate->id . "?tid=" . $currentTime->format('Y-m'));
        $respose->assertSee('value="10:55"', false);
        $respose->assertSee('value="11:55"', false);
    }
}
