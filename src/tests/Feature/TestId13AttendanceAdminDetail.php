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

        $restData = Rest::where('attendance_id', $clockInDate->id)->first();
        $paramsDetail = [
            'attendance_clockin'  => "17:00",  // 出勤時間を退勤時間の後にする
            'attendance_clockout' => "07:52",
            'rest_id'             => $restData['id'],
            'rest_clockin'        => '11:55',
            'rest_clockout'       => '12:56',
            'rest_id1'            => "",
            'rest_clockin1'       => "",
            'rest_clockout1'      => "",
            'descript'            => "テスト11のために修正",
            'id'                  => $clockInDate->id,
            'user_id'             => $this->user->id,
            'name'                => $this->user->name,
            'dateline'            => $currentTime->format('Y-m-01'),
            'status'              => 2,
            'restSectMax'         => 1,
            'gardFlg'             => 1,
            'admin_btn_mod'       => null,  // 修正ボタン押下
        ];
        $respose = $this->post('/stamp_correction_request/approve/' . $clockInDate->id, $paramsDetail)
            ->assertInvalid(['clock_in' => '出勤時間もしくは退勤時間が不適切な値です。']);

        $respose = $this->get('/logout');
    }

    /** @test */
    public function 勤怠詳細情報取得・修正機能（管理者）_休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される()
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

        $restData = Rest::where('attendance_id', $clockInDate->id)->first();
        $paramsDetail = [
            'attendance_clockin'  => "07:52",
            'attendance_clockout' => "17:00",
            'rest_id'             => $restData['id'],
            'rest_clockin'        => '17:55',  // 休憩開始時間が退勤時間の後
            'rest_clockout'       => '18:56',
            'rest_id1'            => "",
            'rest_clockin1'       => "",
            'rest_clockout1'      => "",
            'descript'            => "テスト11のために修正",
            'id'                  => $clockInDate->id,
            'user_id'             => $this->user->id,
            'name'                => $this->user->name,
            'dateline'            => $currentTime->format('Y-m-01'),
            'status'              => 2,
            'restSectMax'         => 1,
            'gardFlg'             => 1,
            'admin_btn_mod'       => null,  // 修正ボタン押下
        ];
        $respose = $this->post('/stamp_correction_request/approve/' . $clockInDate->id, $paramsDetail)
            ->assertInvalid(['rest_in' => '休憩時間が勤務時間外です。']);

        $respose = $this->get('/logout');
    }

    /** @test */
    public function 勤怠詳細情報取得・修正機能（管理者）_休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される()
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

        $restData = Rest::where('attendance_id', $clockInDate->id)->first();
        $paramsDetail = [
            'attendance_clockin'  => "07:52",
            'attendance_clockout' => "17:00",
            'rest_id'             => $restData['id'],
            'rest_clockin'        => '16:55',
            'rest_clockout'       => '18:56',  // 休憩終了時間が退勤時間より後
            'rest_id1'            => "",
            'rest_clockin1'       => "",
            'rest_clockout1'      => "",
            'descript'            => "テスト11のために修正",
            'id'                  => $clockInDate->id,
            'user_id'             => $this->user->id,
            'name'                => $this->user->name,
            'dateline'            => $currentTime->format('Y-m-01'),
            'status'              => 2,
            'restSectMax'         => 1,
            'gardFlg'             => 1,
            'admin_btn_mod'       => null,  // 修正ボタン押下
        ];
        $respose = $this->post('/stamp_correction_request/approve/' . $clockInDate->id, $paramsDetail)
            ->assertInvalid(['rest_out' => '休憩時間が勤務時間外です。']);

        $respose = $this->get('/logout');
    }

    /** @test */
    public function 勤怠詳細情報取得・修正機能（管理者）_備考欄が未入力の場合のエラーメッセージが表示される()
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

        $restData = Rest::where('attendance_id', $clockInDate->id)->first();
        $paramsDetail = [
            'attendance_clockin'  => "07:52",
            'attendance_clockout' => "17:00",
            'rest_id'             => $restData['id'],
            'rest_clockin'        => '11:55',
            'rest_clockout'       => '12:56',
            'rest_id1'            => "",
            'rest_clockin1'       => "",
            'rest_clockout1'      => "",
            'descript'            => "",  // 備考欄を空欄にする
            'id'                  => $clockInDate->id,
            'user_id'             => $this->user->id,
            'name'                => $this->user->name,
            'dateline'            => $currentTime->format('Y-m-01'),
            'status'              => 2,
            'restSectMax'         => 1,
            'gardFlg'             => 1,
            'admin_btn_mod'       => null,  // 修正ボタン押下
        ];
        $respose = $this->post('/stamp_correction_request/approve/' . $clockInDate->id, $paramsDetail)
            ->assertInvalid(['descript' => '備考を記入してください']);

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
