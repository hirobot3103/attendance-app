<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Request_Attendance;
use App\Models\Rest;

use Tests\TestCase;

use Carbon\Carbon;

class TestId15CheckApprove extends TestCase
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
    public function 勤怠情報修正機能（管理者）_承認待ちの修正申請が全て表示されている()
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
            'descript'            => "テスト用",  // 備考欄を空欄にする
            'id'                  => $clockInDate->id,
            'user_id'             => $this->user->id,
            'name'                => $this->user->name,
            'dateline'            => $currentTime->format('Y-m-d'),
            'status'              => 2,
            'restSectMax'         => 1,
            'gardFlg'             => 1,
            'admin_btn_mod'       => null,  // 修正ボタン押下
        ];
        $respose = $this->post('/stamp_correction_request/approve/' . $clockInDate->id, $paramsDetail);

        // 申請一覧を表示
        $respose = $this->get('/stamp_correction_request/list');
        $respose->assertSee("申請一覧", false);
        $respose->assertSee('<span>承認待ち</span>', false);

        $respose = $this->get('/logout');
    }

    /** @test */
    public function 勤怠情報修正機能（管理者）_承認済みの修正申請が全て表示されている()
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
            'descript'            => "テスト用",
            'id'                  => $clockInDate->id,
            'user_id'             => $this->user->id,
            'name'                => $this->user->name,
            'dateline'            => $currentTime->format('Y-m-d'),
            'status'              => 2,
            'restSectMax'         => 1,
            'gardFlg'             => 1,
            'admin_btn_mod'       => null,  // 修正ボタン押下
        ];
        $respose = $this->post('/stamp_correction_request/approve/' . $clockInDate->id, $paramsDetail);

        // 申請一覧(承認済み)を表示
        $respose = $this->get('/stamp_correction_request/list/11');

        $respose->assertSee("申請一覧", false);
        $respose->assertSee('<span>承認済み</span>', false);

        $respose = $this->get('/logout');
    }

    /** @test */
    public function 勤怠情報修正機能（管理者）_修正申請の詳細内容が正しく表示されている()
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

        // 勤怠詳細を表示
        $paramDetail = [
            'tid' => $currentTime->format('Y-m-d'),
            'uid' => 6,
        ];
        $respose = $this->post('/admin/attendance/staff/detail/' . $clockInDate->id, $paramDetail);

        $restData = Rest::where('attendance_id', $clockInDate->id)->first();
        $paramsDetail = [
            'attendance_clockin'  => "07:52",
            'attendance_clockout' => "17:12",    // 修正申請箇所
            'rest_id'             => $restData['id'],
            'rest_clockin'        => '11:55',
            'rest_clockout'       => '12:56',
            'rest_id1'            => "",
            'rest_clockin1'       => "",
            'rest_clockout1'      => "",
            'descript'            => "テスト用",
            'id'                  => $clockInDate->id,
            'user_id'             => $this->user->id,
            'name'                => $this->user->name,
            'dateline'            => $currentTime->format('Y-m-d'),
            'status'              => 2,
            'restSectMax'         => 1,
            'gardFlg'             => 1,
            'admin_btn_mod'       => null,  // 修正ボタン押下
        ];
        $respose = $this->post('/stamp_correction_request/approve/' . $clockInDate->id, $paramsDetail);

        // 申請一覧(承認待ち)を表示
        $respose = $this->get('/stamp_correction_request/list');

        // 詳細表示
        $reqDatas = Request_Attendance::where('attendance_id', $clockInDate->id)->first();
        $respose = $this->get('/stamp_correction_request/' . $reqDatas->id);

        $respose->assertSee($this->user->name, false);
        $respose->assertSee($currentTime->format('Y年'), false);
        $respose->assertSee($currentTime->format('m月d日'), false);
        $respose->assertSee("07:52", false);
        $respose->assertSee("17:12", false);
        $respose->assertSee("11:55", false);
        $respose->assertSee("12:56", false);

        $respose = $this->get('/logout');
    }

    /** @test */
    public function 勤怠情報修正機能（管理者）_修正申請の承認処理が正しく行われる()
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

        // 勤怠詳細を表示
        $paramDetail = [
            'tid' => $currentTime->format('Y-m-d'),
            'uid' => 6,
        ];
        $respose = $this->post('/admin/attendance/staff/detail/' . $clockInDate->id, $paramDetail);

        $restData = Rest::where('attendance_id', $clockInDate->id)->first();
        $paramsDetail = [
            'attendance_clockin'  => "07:52",
            'attendance_clockout' => "17:12",    // 修正申請箇所
            'rest_id'             => $restData['id'],
            'rest_clockin'        => '11:55',
            'rest_clockout'       => '12:56',
            'rest_id1'            => "",
            'rest_clockin1'       => "",
            'rest_clockout1'      => "",
            'descript'            => "テスト用",
            'id'                  => $clockInDate->id,
            'user_id'             => $this->user->id,
            'name'                => $this->user->name,
            'dateline'            => $currentTime->format('Y-m-d'),
            'status'              => 2,
            'restSectMax'         => 1,
            'gardFlg'             => 1,
            'admin_btn_mod'       => null,  // 修正ボタン押下
        ];
        $respose = $this->post('/stamp_correction_request/approve/' . $clockInDate->id, $paramsDetail);

        // 申請一覧(承認待ち)を表示
        $respose = $this->get('/stamp_correction_request/list');

        // 詳細表示
        $reqDatas = Request_Attendance::where('attendance_id', $clockInDate->id)->first();
        $respose = $this->get('/stamp_correction_request/' . $reqDatas->id);

        $paramsDetail = [
            'attendance_clockin'  => "07:52",
            'attendance_clockout' => "17:12",    // 修正申請箇所
            'rest_id'             => $restData['id'],
            'rest_clockin'        => '11:55',
            'rest_clockout'       => '12:56',
            'rest_id1'            => "",
            'rest_clockin1'       => "",
            'rest_clockout1'      => "",
            'descript'            => "テスト用",
            'id'                  => $clockInDate->id,
            'user_id'             => $this->user->id,
            'name'                => $this->user->name,
            'dateline'            => $currentTime->format('Y-m-d'),
            'status'              => 12,
            'restSectMax'         => 1,
            'gardFlg'             => 1,
        ];
        $respose = $this->post('/stamp_correction_request/approve/' . $reqDatas->id, $paramsDetail);

        $respose = $this->get('stamp_correction_request/list/11');
        $respose->assertSee($this->user->name, false);
        $respose->assertSee($currentTime->format('Y/m/d'), false);
        $respose->assertSee("テスト用", false);

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
