<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Rest;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class TestId13AttendanceAdminDetail extends TestCase
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
    public function 勤怠詳細情報取得・修正機能（管理者）_勤怠詳細画面に表示されるデータが選択したものになっている()
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
        $respose = $this->get('/admin/attendance/list');

        // 勤怠詳細を表示
        $paramDetail = [
            'tid' => $currentTime->format('Y-m-d'),
            'uid' => 6,
        ];
        $respose = $this->post('/admin/attendance/staff/detail/' . $clockInDate->id, $paramDetail);

        // 作成したデータが詳細表示にあるか確認
        $respose->assertSee($this->user->name);
        $respose->assertSee($currentTime->format('Y年'));
        $respose->assertSee($currentTime->format('m月d日'));
        $respose->assertSee("07:52");
        $respose->assertSee("17:00");
        $respose->assertSee("11:55");
        $respose->assertSee("12:56");

        $respose = $this->get('/logout');
    }


    /** @test */
    public function 勤怠詳細情報取得・修正機能（管理者）_出勤時間が退勤時間より後になっている場合エラーメッセージが表示される()
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
        $respose = $this->get('/admin/attendance/list');

        // 勤怠詳細を表示
        $paramDetail = [
            'tid' => $currentTime->format('Y-m-d'),
            'uid' => 6,
        ];
        $respose = $this->post('/admin/attendance/staff/detail/' . $clockInDate->id, $paramDetail);

        // 作成したデータが詳細表示にあるか確認
        $respose->assertSee($this->user->name);
        $respose->assertSee($currentTime->format('Y年'));
        $respose->assertSee($currentTime->format('m月d日'));
        $respose->assertSee("07:52");
        $respose->assertSee("17:00");
        $respose->assertSee("11:55");
        $respose->assertSee("12:56");

        $respose = $this->get('/logout');
    }

    // 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    // /** @test */
    // public function 勤怠一覧情報取得機能（管理者）_遷移した際に現在の日付が表示される()
    // {
    //     // 勤怠データを作成
    //     $currentTime = new Carbon();
    //     $params = [
    //         'user_id'  => $this->user->id,
    //         'clock_in' => $currentTime->format('Y-m-d') . " 00:52:59",
    //         'clock_out' => $currentTime->format('Y-m-d') . " 01:00:00",
    //         'status'   => 2,
    //     ];

    //     Attendance::create($params);
    //     $clockInDate = Attendance::where('user_id', 6)->first();

    //     $paramsRest = [
    //         'attendance_id'  => $clockInDate->id,
    //         'rest_in' => $currentTime->format('Y-m-d') . " 00:55:59",
    //         'rest_out' => $currentTime->format('Y-m-d') . " 00:56:00",
    //     ];
    //     Rest::create($paramsRest);

    //     // 管理者でログイン
    //     $respose = $this->get('/admin/login');
    //     $admin = Admin::where('id', 1)->first();
    //     $this->actingAs($admin, 'admin');

    //     // 勤怠ページ一覧を取得
    //     $respose = $this->get('/admin/attendance/list');
    //     $respose->assertSee('value="' . $currentTime->format('Y-m-d') . '"', false);

    //     $respose = $this->post('/logout');
    // }

    // /** @test */
    // public function 勤怠一覧情報取得機能（管理者）_「前日」を押下した時に前の日の勤怠情報が表示される()
    // {
    //     // 勤怠データを作成
    //     $currentTime = new Carbon();  // 当月用
    //     $prevTime = new Carbon();     // 前月用
    //     $prevDate = $prevTime->subDays(1);
    //     $params = [
    //         'user_id'  => $this->user->id,
    //         'clock_in' => $prevDate->format('Y-m-d') . " 07:52:59",
    //         'clock_out' => $prevDate->format('Y-m-d') . " 17:00:00",
    //         'status'   => 2,
    //     ];

    //     Attendance::create($params);
    //     $clockInDate = Attendance::where('user_id', 6)->first();

    //     $paramsRest = [
    //         'attendance_id'  => $clockInDate->id,
    //         'rest_in' => $prevDate->format('Y-m-d') . "  10:55:59",
    //         'rest_out' => $prevDate->format('Y-m-d') . " 11:55:00",
    //     ];
    //     Rest::create($paramsRest);

    //     // 表示されるデータと比較するため、登録されたデータを成形
    //     $clockInTime = new Carbon($clockInDate->clock_in);
    //     $clockOutTime = new Carbon($clockInDate->clock_out);
    //     $recordDiffMin = $this->getAttendanceTimes($clockInDate); // 勤務時間（休憩時間考慮なし）
    //     $recordDiffRest = $this->getRestTimes($clockInDate->id);  // 休憩時間通算
    //     $recordDiffTotal = $recordDiffMin - $recordDiffRest;      // 勤務時間（休憩時間考慮）

    //     // 分で取得した休憩時間を時間と分に変換
    //     $retDiff = '0:00';
    //     $hours = floor((int)$recordDiffRest / 60);
    //     $remainingMinutes = (int)$recordDiffRest % 60;
    //     if ($remainingMinutes < 10) {
    //         $retDiff = "{$hours}:0{$remainingMinutes}";
    //     } else {
    //         $retDiff = "{$hours}:{$remainingMinutes}";
    //     }
    //     $defRest = $retDiff;

    //     // 分で取得した勤務時間を時間と分に変換
    //     $retDiff = '0:00';
    //     $hours = floor((int)$recordDiffTotal / 60);
    //     $remainingMinutes = (int)$recordDiffTotal % 60;
    //     if ($remainingMinutes < 10) {
    //         $retDiff = "{$hours}:0{$remainingMinutes}";
    //     } else {
    //         $retDiff = "{$hours}:{$remainingMinutes}";
    //     }
    //     $defTotal = $retDiff;

    //     // 勤怠ページ一覧を取得
    //     // 管理者でログイン
    //     $respose = $this->get('/admin/login');
    //     $admin = Admin::where('id', 1)->first();
    //     $this->actingAs($admin, 'admin');

    //     // 勤怠ページ一覧を取得
    //     $respose = $this->get('/admin/attendance/list');
    //     $pramPrev = [
    //         'day_prev' => null,
    //         'day__current' => $currentTime->format('Y-m-d'),
    //     ];
    //     $respose = $this->post('/admin/attendance/list', $pramPrev);
    //     $respose->assertSee('<td>' . $clockInTime->format('H:i') . '</td>', false);
    //     $respose->assertSee('<td>' . $clockOutTime->format('H:i') . '</td>', false);
    //     $respose->assertSee('<td>' . $defRest . '</td>', false);
    //     $respose->assertSee('<td>' . $defTotal . '</td>', false);

    //     $respose = $this->post('/logout');
    // }

    // /** @test */
    // public function 勤怠一覧情報取得機能（管理者）_「翌日」を押下した時に次の日の勤怠情報が表示される()
    // {
    //     // 勤怠データを作成
    //     $currentTime = new Carbon();  // 当月用
    //     $baseTime = $currentTime->subMonths(1);
    //     $prevTime = new Carbon($baseTime);     //　当月に相当するデータ

    //     // 翌月に相当するデータ
    //     $prevDate = $prevTime->addMonths(1);
    //     $params = [
    //         'user_id'  => $this->user->id,
    //         'clock_in' => $prevDate->format('Y-m-d') . " 07:52:59",
    //         'clock_out' => $prevDate->format('Y-m-d') . " 17:00:00",
    //         'status'   => 2,
    //     ];

    //     Attendance::create($params);
    //     $clockInDate = Attendance::where('user_id', 6)->first();

    //     $paramsRest = [
    //         'attendance_id'  => $clockInDate->id,
    //         'rest_in' => $prevDate->format('Y-m-d') . "  10:55:59",
    //         'rest_out' => $prevDate->format('Y-m-d') . " 11:55:00",
    //     ];
    //     Rest::create($paramsRest);

    //     // 表示されるデータと比較するため、登録されたデータを成形
    //     $clockInTime = new Carbon($clockInDate->clock_in);
    //     $clockOutTime = new Carbon($clockInDate->clock_out);
    //     $recordDiffMin = $this->getAttendanceTimes($clockInDate); // 勤務時間（休憩時間考慮なし）
    //     $recordDiffRest = $this->getRestTimes($clockInDate->id);  // 休憩時間通算
    //     $recordDiffTotal = $recordDiffMin - $recordDiffRest;      // 勤務時間（休憩時間考慮）

    //     // 分で取得した休憩時間を時間と分に変換
    //     $retDiff = '0:00';
    //     $hours = floor((int)$recordDiffRest / 60);
    //     $remainingMinutes = (int)$recordDiffRest % 60;
    //     if ($remainingMinutes < 10) {
    //         $retDiff = "{$hours}:0{$remainingMinutes}";
    //     } else {
    //         $retDiff = "{$hours}:{$remainingMinutes}";
    //     }
    //     $defRest = $retDiff;

    //     // 分で取得した勤務時間を時間と分に変換
    //     $retDiff = '0:00';
    //     $hours = floor((int)$recordDiffTotal / 60);
    //     $remainingMinutes = (int)$recordDiffTotal % 60;
    //     if ($remainingMinutes < 10) {
    //         $retDiff = "{$hours}:0{$remainingMinutes}";
    //     } else {
    //         $retDiff = "{$hours}:{$remainingMinutes}";
    //     }
    //     $defTotal = $retDiff;

    //     // 勤怠ページ一覧を取得
    //     // 管理者でログイン
    //     $respose = $this->get('/admin/login');
    //     $admin = Admin::where('id', 1)->first();
    //     $this->actingAs($admin, 'admin');

    //     // 勤怠ページ一覧を取得
    //     $respose = $this->get('/admin/attendance/list');
    //     $pramPrev = [
    //         'day_next' => null,
    //         'day__current' => $prevTime->format('Y-m-d'),
    //     ];
    //     $respose = $this->post('/admin/attendance/list', $pramPrev);
    //     $respose->assertSee('<td>' . $clockInTime->format('H:i') . '</td>', false);
    //     $respose->assertSee('<td>' . $clockOutTime->format('H:i') . '</td>', false);
    //     $respose->assertSee('<td>' . $defRest . '</td>', false);
    //     $respose->assertSee('<td>' . $defTotal . '</td>', false);

    //     $respose = $this->post('/logout');
    // }

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
