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

class TestId09AttendanceList extends TestCase
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
    public function 勤怠一覧情報取得機能（一般ユーザー）_自分が行った勤怠情報が全て表示されている()
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

        // 表示されるデータと比較するため、登録されたデータを成形
        $clockInTime = new Carbon($clockInDate->clock_in);
        $clockOutTime = new Carbon($clockInDate->clock_out);
        $recordDiffMin = $this->getAttendanceTimes($clockInDate); // 勤務時間（休憩時間考慮なし）
        $recordDiffRest = $this->getRestTimes($clockInDate->id);  // 休憩時間通算
        $recordDiffTotal = $recordDiffMin - $recordDiffRest;      // 勤務時間（休憩時間考慮）

        // 分で取得した休憩時間を時間と分に変換
        $retDiff = '0:00';
        $hours = floor((int)$recordDiffRest / 60);
        $remainingMinutes = (int)$recordDiffRest % 60;
        if ($remainingMinutes < 10) {
            $retDiff = "{$hours}:0{$remainingMinutes}";
        } else {
            $retDiff = "{$hours}:{$remainingMinutes}";
        }
        $defRest = $retDiff;

        // 分で取得した勤務時間を時間と分に変換
        $retDiff = '0:00';
        $hours = floor((int)$recordDiffTotal / 60);
        $remainingMinutes = (int)$recordDiffTotal % 60;
        if ($remainingMinutes < 10) {
            $retDiff = "{$hours}:0{$remainingMinutes}";
        } else {
            $retDiff = "{$hours}:{$remainingMinutes}";
        }
        $defTotal = $retDiff;

        // 勤怠ページ一覧を取得
        $respose = $this->get('/attendance/list');
        $respose->assertSee('<td>' . $clockInTime->format('H:i') . '</td>', false);
        $respose->assertSee('<td>' . $clockOutTime->format('H:i') . '</td>', false);
        $respose->assertSee('<td>' . $defRest . '</td>', false);
        $respose->assertSee('<td>' . $defTotal . '</td>', false);
    }

    /** @test */
    public function 勤怠一覧情報取得機能（一般ユーザー）_勤怠一覧画面に遷移した際に現在の月が表示される()
    {

        $respose = $this->get('/login');
        $this->actingAs($this->user);

        $currentTime = new Carbon();
        $checkString = 'value="' . $currentTime->format('Y-m') . '"';
        $respose = $this->get('/attendance/list');
        $respose->assertSee($checkString, false);
    }

    /** @test */
    public function 勤怠一覧情報取得機能（一般ユーザー）_「前月」を押下した時に表示月の前月の情報が表示される()
    {
        // 勤怠データを作成
        $currentTime = new Carbon();  // 当月用
        $prevTime = new Carbon();     // 前月用
        $prevDate = $prevTime->subMonths(1);
        $params = [
            'user_id'  => $this->user->id,
            'clock_in' => $prevDate->format('Y-m-d') . " 07:52:59",
            'clock_out' => $prevDate->format('Y-m-d') . " 17:00:00",
            'status'   => 2,
        ];

        Attendance::create($params);
        $clockInDate = Attendance::where('user_id', 6)->first();

        $paramsRest = [
            'attendance_id'  => $clockInDate->id,
            'rest_in' => $prevDate->format('Y-m-d') . "  10:55:59",
            'rest_out' => $prevDate->format('Y-m-d') . " 11:55:00",
        ];
        Rest::create($paramsRest);

        $respose = $this->get('/login');
        $this->actingAs($this->user);

        $respose = $this->get('/attendance/list');
        $respose = $this->post('/attendance/list', [
            'month_prev' => null,
            'month__current' => $currentTime->format('Y-m'),
        ]);

        // 表示されるデータと比較するため、登録されたデータを成形
        $clockInTime = new Carbon($clockInDate->clock_in);
        $clockOutTime = new Carbon($clockInDate->clock_out);
        $recordDiffMin = $this->getAttendanceTimes($clockInDate); // 勤務時間（休憩時間考慮なし）
        $recordDiffRest = $this->getRestTimes($clockInDate->id);  // 休憩時間通算
        $recordDiffTotal = $recordDiffMin - $recordDiffRest;      // 勤務時間（休憩時間考慮）

        // 分で取得した休憩時間を時間と分に変換
        $retDiff = '0:00';
        $hours = floor((int)$recordDiffRest / 60);
        $remainingMinutes = (int)$recordDiffRest % 60;
        if ($remainingMinutes < 10) {
            $retDiff = "{$hours}:0{$remainingMinutes}";
        } else {
            $retDiff = "{$hours}:{$remainingMinutes}";
        }
        $defRest = $retDiff;

        // 分で取得した勤務時間を時間と分に変換
        $retDiff = '0:00';
        $hours = floor((int)$recordDiffTotal / 60);
        $remainingMinutes = (int)$recordDiffTotal % 60;
        if ($remainingMinutes < 10) {
            $retDiff = "{$hours}:0{$remainingMinutes}";
        } else {
            $retDiff = "{$hours}:{$remainingMinutes}";
        }
        $defTotal = $retDiff;

        // 勤怠ページ一覧を取得
        $respose->assertSee('<td>' . $clockInTime->format('H:i') . '</td>', false);
        $respose->assertSee('<td>' . $clockOutTime->format('H:i') . '</td>', false);
        $respose->assertSee('<td>' . $defRest . '</td>', false);
        $respose->assertSee('<td>' . $defTotal . '</td>', false);
    }

    /** @test */
    public function 勤怠一覧情報取得機能（一般ユーザー）_「翌月」を押下した時に表示月の前月の情報が表示される()
    {
        // 勤怠データを作成
        $currentTime = new Carbon();  // 当月用
        $baseTime = $currentTime->subMonths(1);
        $prevTime = new Carbon($baseTime);     //　当月に相当するデータ

        // 翌月に相当するデータ
        $prevDate = $prevTime->addMonths(1);
        $params = [
            'user_id'  => $this->user->id,
            'clock_in' => $prevDate->format('Y-m-d') . " 07:52:59",
            'clock_out' => $prevDate->format('Y-m-d') . " 17:00:00",
            'status'   => 2,
        ];

        Attendance::create($params);
        $clockInDate = Attendance::where('user_id', 6)->first();

        $paramsRest = [
            'attendance_id'  => $clockInDate->id,
            'rest_in' => $prevDate->format('Y-m-d') . "  10:55:59",
            'rest_out' => $prevDate->format('Y-m-d') . " 11:55:00",
        ];
        Rest::create($paramsRest);

        $respose = $this->get('/login');
        $this->actingAs($this->user);

        $respose = $this->get('/attendance/list');
        $respose = $this->post('/attendance/list', [
            'month_next' => null,
            'month__current' => $prevTime->format('Y-m'),
        ]);

        // 表示されるデータと比較するため、登録されたデータを成形
        $clockInTime = new Carbon($clockInDate->clock_in);
        $clockOutTime = new Carbon($clockInDate->clock_out);
        $recordDiffMin = $this->getAttendanceTimes($clockInDate); // 勤務時間（休憩時間考慮なし）
        $recordDiffRest = $this->getRestTimes($clockInDate->id);  // 休憩時間通算
        $recordDiffTotal = $recordDiffMin - $recordDiffRest;      // 勤務時間（休憩時間考慮）

        // 分で取得した休憩時間を時間と分に変換
        $retDiff = '0:00';
        $hours = floor((int)$recordDiffRest / 60);
        $remainingMinutes = (int)$recordDiffRest % 60;
        if ($remainingMinutes < 10) {
            $retDiff = "{$hours}:0{$remainingMinutes}";
        } else {
            $retDiff = "{$hours}:{$remainingMinutes}";
        }
        $defRest = $retDiff;

        // 分で取得した勤務時間を時間と分に変換
        $retDiff = '0:00';
        $hours = floor((int)$recordDiffTotal / 60);
        $remainingMinutes = (int)$recordDiffTotal % 60;
        if ($remainingMinutes < 10) {
            $retDiff = "{$hours}:0{$remainingMinutes}";
        } else {
            $retDiff = "{$hours}:{$remainingMinutes}";
        }
        $defTotal = $retDiff;

        // 勤怠ページ一覧を取得
        $respose->assertSee('<td>' . $clockInTime->format('H:i') . '</td>', false);
        $respose->assertSee('<td>' . $clockOutTime->format('H:i') . '</td>', false);
        $respose->assertSee('<td>' . $defRest . '</td>', false);
        $respose->assertSee('<td>' . $defTotal . '</td>', false);
    }

    /** @test */
    public function 勤怠一覧情報取得機能（一般ユーザー）_「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
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
        $respose->assertSee('<div class="attendance-title">勤怠詳細</div>', false);
    }

    private function getAttendanceTimes($date)
    {
        $startTime = new Carbon($date->clock_in);
        $endTime   = new Carbon($date->clock_out);
        return $startTime->diffInMinutes($endTime);
    }

    private function getRestTimes($attendId)
    {
        return DB::table('rests')
            ->where('attendance_id', $attendId)
            ->whereNotNull('rest_out')
            ->sum(DB::raw('TIMESTAMPDIFF(MINUTE, rest_in, rest_out)'));
    }
}
