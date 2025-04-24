<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Rest;

use Tests\TestCase;

use Carbon\Carbon;

class TestId14GetStaffData extends TestCase
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
    public function ユーザー情報取得機能（管理者）_管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる()
    {
        // 勤怠データを作成
        $currentTime = new Carbon();
        $params = [
            'user_id'  => $this->user->id,
            'clock_in' => $currentTime->format('Y-m-d') . " 07:52:59",
            'clock_out' => $currentTime->format('Y-m-d') . " 17:00:00",
            'status'   => 2,
        ];

        Attendance::create($params);
        $clockInDate = Attendance::where('user_id', 6)->first();

        // 休憩データの作成
        $paramsRest = [
            'attendance_id'  => $clockInDate->id,
            'rest_in' => $currentTime->format('Y-m-d') . " 11:55:59",
            'rest_out' => $currentTime->format('Y-m-d') . " 12:56:00",
        ];
        Rest::create($paramsRest);

        // 管理者でログイン
        $respose = $this->get('/admin/login');
        $admin = Admin::where('id', 1)->first();
        $this->actingAs($admin, 'admin');

        // 勤怠一覧を表示
        $respose = $this->get('/admin/staff/list');
        $respose->assertSee($this->user->name);
        $respose->assertSee($this->user->email);

        $respose = $this->get('/logout');
    }

    /** @test */
    public function ユーザー情報取得機能（管理者）_ユーザーの勤怠情報が正しく表示される()
    {
        // 勤怠データを作成
        $currentTime = new Carbon();
        $params = [
            'user_id'  => $this->user->id,
            'clock_in' => $currentTime->format('Y-m-d') . " 07:52:59",
            'clock_out' => $currentTime->format('Y-m-d') . " 17:00:00",
            'status'   => 2,
        ];

        Attendance::create($params);
        $clockInDate = Attendance::where('user_id', 6)->first();

        // 休憩データの作成
        $paramsRest = [
            'attendance_id'  => $clockInDate->id,
            'rest_in' => $currentTime->format('Y-m-d') . " 11:55:59",
            'rest_out' => $currentTime->format('Y-m-d') . " 12:56:00",
        ];
        Rest::create($paramsRest);

        // 管理者でログイン
        $respose = $this->get('/admin/login');
        $admin = Admin::where('id', 1)->first();
        $this->actingAs($admin, 'admin');

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

        // 勤怠一覧を表示
        $respose = $this->get('/admin/attendance/staff/6');
        // $paramDetailStaff = [
        //     'tid' => $currentTime->format('Y-m-d'),
        //     'uid' => $clockInDate->user_id,
        // ];
        // $respose = $this->post('/admin/attendance/staff/detail/' . $clockInDate->id, $paramDetailStaff);
        $respose->assertSee($this->user->name, false);
        $respose->assertSee($clockInTime->format('H:i'), false);
        $respose->assertSee($clockOutTime->format('H:i'), false);
        $respose->assertSee($defRest, false);
        $respose->assertSee($defTotal, false);

        $respose = $this->get('/logout');
    }

    /** @test */
    public function ユーザー情報取得機能（管理者）_「前月」を押下した時に表示月の前月の情報が表示される()
    {
        // 勤怠データを作成
        $currentTime = new Carbon();  // 当月用
        $prevTime = new Carbon();     // 前月用
        $prevDate = $prevTime->subMonths(1);
        $params = [
            'user_id'  => $this->user->id,
            'clock_in' => $prevDate->format('Y-m') . "-01 07:52:59",
            'clock_out' => $prevDate->format('Y-m') . "-01 17:00:00",
            'status'   => 2,
        ];

        Attendance::create($params);
        $clockInDate = Attendance::where('user_id', 6)->first();

        $paramsRest = [
            'attendance_id'  => $clockInDate->id,
            'rest_in' => $prevDate->format('Y-m') . "-01 10:55:59",
            'rest_out' => $prevDate->format('Y-m') . "-01 11:55:00",
        ];
        Rest::create($paramsRest);

        // 管理者でログイン
        $respose = $this->get('/admin/login');
        $admin = Admin::where('id', 1)->first();
        $this->actingAs($admin, 'admin');

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

        // 勤怠一覧を表示
        $respose = $this->get('/admin/attendance/staff/6');
        $pramPrev = [
            'month_prev'     => null,
            'month__current' => $currentTime->format('Y-m'),
            'select_user_id' => 6,

        ];
        $respose = $this->post('/admin/attendance/staff/6', $pramPrev);
        $respose->assertSee($this->user->name, false);
        $respose->assertSee($clockInTime->format('H:i'), false);
        $respose->assertSee($clockOutTime->format('H:i'), false);
        $respose->assertSee($defRest, false);
        $respose->assertSee($defTotal, false);

        $respose = $this->get('/logout');
    }

    /** @test */
    public function ユーザー情報取得機能（管理者）_翌月」を押下した時に表示月の前月の情報が表示される()
    {
        // 勤怠データを作成
        $currentTimeBase = new Carbon();  // 当月用
        $currentTime = $currentTimeBase->subMonths(1);  // 当月用

        $prevDate = new Carbon();  // 翌月用
        $params = [
            'user_id'  => $this->user->id,
            'clock_in' => $prevDate->format('Y-m') . "-01 07:52:59",
            'clock_out' => $prevDate->format('Y-m') . "-01 17:00:00",
            'status'   => 2,
        ];

        Attendance::create($params);
        $clockInDate = Attendance::where('user_id', 6)->first();

        $paramsRest = [
            'attendance_id'  => $clockInDate->id,
            'rest_in' => $prevDate->format('Y-m') . "-01 10:55:59",
            'rest_out' => $prevDate->format('Y-m') . "-01 11:55:00",
        ];
        Rest::create($paramsRest);

        // 管理者でログイン
        $respose = $this->get('/admin/login');
        $admin = Admin::where('id', 1)->first();
        $this->actingAs($admin, 'admin');

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

        // 勤怠一覧を表示
        $respose = $this->get('/admin/attendance/staff/6');
        $pramPrev = [
            'month_next'     => null,
            'month__current' => $currentTime->format('Y-m'),
            'select_user_id' => 6,

        ];
        $respose = $this->post('/admin/attendance/staff/6', $pramPrev);
        $respose->assertSee($this->user->name, false);
        $respose->assertSee($clockInTime->format('H:i'), false);
        $respose->assertSee($clockOutTime->format('H:i'), false);
        $respose->assertSee($defRest, false);
        $respose->assertSee($defTotal, false);

        $respose = $this->get('/logout');
    }

    /** @test */
    public function ユーザー情報取得機能（管理者）_「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
    {
        // 勤怠データを作成
        $currentTime = new Carbon();
        $params = [
            'user_id'  => $this->user->id,
            'clock_in' => $currentTime->format('Y-m-d') . " 07:52:59",
            'clock_out' => $currentTime->format('Y-m-d') . " 17:00:00",
            'status'   => 2,
        ];

        Attendance::create($params);
        $clockInDate = Attendance::where('user_id', 6)->first();

        // 休憩データの作成
        $paramsRest = [
            'attendance_id'  => $clockInDate->id,
            'rest_in' => $currentTime->format('Y-m-d') . " 11:55:59",
            'rest_out' => $currentTime->format('Y-m-d') . " 12:56:00",
        ];
        Rest::create($paramsRest);

        // 管理者でログイン
        $respose = $this->get('/admin/login');
        $admin = Admin::where('id', 1)->first();
        $this->actingAs($admin, 'admin');

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

        // 勤怠一覧を表示
        $respose = $this->get('/admin/attendance/staff/6');
        $paramDetail = [
            'tid' => $currentTime->format('Y-m-d'),
            'uid' => 6,
        ];
        $respose = $this->post('/admin/attendance/staff/detail/' . $clockInDate->id, $paramDetail);

        // 作成したデータが詳細表示にあるか確認
        $respose->assertSee($this->user->name, false);
        $respose->assertSee($currentTime->format('Y年'), false);
        $respose->assertSee($currentTime->format('m月d日'), false);
        $respose->assertSee("07:52", false);
        $respose->assertSee("17:00", false);
        $respose->assertSee("11:55", false);
        $respose->assertSee("12:56", false);

        $respose = $this->get('/logout');
    }

    // 勤務時間（休憩時間を考慮しない）を算出
    private function getAttendanceTimes($date)
    {
        $startTime = new Carbon($date->clock_in);
        $endTime   = new Carbon($date->clock_out);
        return $startTime->diffInMinutes($endTime);
    }

    // 休憩時間通算を取得
    private function getRestTimes($attendId)
    {
        return DB::table('rests')
            ->where('attendance_id', $attendId)
            ->whereNotNull('rest_out')
            ->sum(DB::raw('TIMESTAMPDIFF(MINUTE, rest_in, rest_out)'));
    }
}
