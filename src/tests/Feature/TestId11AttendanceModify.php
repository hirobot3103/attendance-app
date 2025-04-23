<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Request_Attendance;
use App\Models\Rest;
use App\Models\Request_Rest;

use Tests\TestCase;

use Carbon\Carbon;

class TestId11AttendanceModify extends TestCase
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
  public function 勤怠詳細情報修正機能（一般ユーザー）_出勤時間が退勤時間より後になっている場合エラーメッセージが表示される()
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

    // ログイン
    $respose = $this->get('/login');
    $this->actingAs($this->user);

    // 詳細表示
    $respose = $this->get('/attendance/' . $clockInDate->id . "?tid=" . $currentTime->format('Y-m'));

    $restData = Rest::where('attendance_id', $clockInDate->id)->first();
    $paramsDetail = [
      'attendance_clockin'  => "17:00",  // 出勤時間を退勤時間の後にする
      'attendance_clockout' => "07:52",
      'rest_id'             => $restData['id'],
      'rest_clockin'        => '10:55',
      'rest_clockout'       => '11:55',
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
      'gardFlg'             => 0,
    ];
    $respose = $this->post('/attendance/' . $clockInDate->id, $paramsDetail)
      ->assertInvalid(['clock_in' => '出勤時間もしくは退勤時間が不適切な値です。']);
  }

  /** @test */
  public function 勤怠詳細情報修正機能（一般ユーザー）_休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される()
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

    // ログイン
    $respose = $this->get('/login');
    $this->actingAs($this->user);

    // 詳細表示
    $respose = $this->get('/attendance/' . $clockInDate->id . "?tid=" . $currentTime->format('Y-m'));

    $restData = Rest::where('attendance_id', $clockInDate->id)->first();
    $paramsDetail = [
      'attendance_clockin'  => "07:52",
      'attendance_clockout' => "17:00",
      'rest_id'             => $restData['id'],
      'rest_clockin'        => '17:55',    // 休憩開始時間を退勤時間の後にする
      'rest_clockout'       => '18:55',
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
      'gardFlg'             => 0,
    ];
    $respose = $this->post('/attendance/' . $clockInDate->id, $paramsDetail)
      ->assertInvalid(['rest_in' => '休憩時間が勤務時間外です。']);
  }

  /** @test */
  public function 勤怠詳細情報修正機能（一般ユーザー）_休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される()
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

    // ログイン
    $respose = $this->get('/login');
    $this->actingAs($this->user);

    // 詳細表示
    $respose = $this->get('/attendance/' . $clockInDate->id . "?tid=" . $currentTime->format('Y-m'));

    $restData = Rest::where('attendance_id', $clockInDate->id)->first();
    $paramsDetail = [
      'attendance_clockin'  => "07:52",
      'attendance_clockout' => "17:00",
      'rest_id'             => $restData['id'],
      'rest_clockin'        => '10:55',
      'rest_clockout'       => '18:55',  // // 休憩狩猟時間を退勤時間の後にする
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
      'gardFlg'             => 0,
    ];
    $respose = $this->post('/attendance/' . $clockInDate->id, $paramsDetail)
      ->assertInvalid(['rest_out' => '休憩時間が勤務時間外です。']);
  }

  /** @test */
  public function 勤怠詳細情報修正機能（一般ユーザー）_備考欄が未入力の場合のエラーメッセージが表示される()
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

    // ログイン
    $respose = $this->get('/login');
    $this->actingAs($this->user);

    // 詳細表示
    $respose = $this->get('/attendance/' . $clockInDate->id . "?tid=" . $currentTime->format('Y-m'));

    $restData = Rest::where('attendance_id', $clockInDate->id)->first();
    $paramsDetail = [
      'attendance_clockin'  => "07:52",
      'attendance_clockout' => "17:00",
      'rest_id'             => $restData['id'],
      'rest_clockin'        => '10:55',
      'rest_clockout'       => '11:55',
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
      'gardFlg'             => 0,
    ];
    $respose = $this->post('/attendance/' . $clockInDate->id, $paramsDetail)
      ->assertInvalid(['descript' => '備考を記入してください']);
  }

  /** @test */
  public function 勤怠詳細情報修正機能（一般ユーザー）_修正申請処理が実行される()
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

    // ログイン
    $respose = $this->get('/login');
    $this->actingAs($this->user);

    // 詳細表示
    $respose = $this->get('/attendance/' . $clockInDate->id . "?tid=" . $currentTime->format('Y-m'));

    $restData = Rest::where('attendance_id', $clockInDate->id)->first();
    $paramsDetail = [
      'attendance_clockin'  => "07:55",  // 修正箇所
      'attendance_clockout' => "17:00",
      'rest_id'             => $restData['id'],
      'rest_clockin'        => '10:55',
      'rest_clockout'       => '11:55',
      'rest_id1'            => "",
      'rest_clockin1'       => "",
      'rest_clockout1'      => "",
      'descript'            => "テスト11のため",
      'id'                  => $clockInDate->id,
      'user_id'             => $this->user->id,
      'name'                => $this->user->name,
      'dateline'            => $currentTime->format('Y-m-01'),
      'status'              => 2,
      'restSectMax'         => 1,
      'gardFlg'             => 0,
    ];
    $respose = $this->post('/attendance/' . $clockInDate->id, $paramsDetail);
    $respose = $this->post('/logout');

    // 管理者でログイン
    $respose = $this->get('/admin/login');
    $admin = Admin::where('id', 1)->first();
    $this->actingAs($admin, 'admin');

    // 申請一覧
    $respose = $this->get('/stamp_correction_request/list');
    $respose->assertSee('テスト11');

    // 申請詳細
    $reqData = Request_Attendance::where('attendance_id', $clockInDate->id)->first();
    $respose = $this->get('/stamp_correction_request/' . $reqData->id);
    $respose->assertSee('テスト11');

    $respose = $this->post('/logout');
  }

  /** @test */
  public function 勤怠詳細情報修正機能（一般ユーザー）_「承認待ち」にログインユーザーが行った申請が全て表示されていること()
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

    // ログイン
    $respose = $this->get('/login');
    $this->actingAs($this->user);

    // 詳細表示
    $respose = $this->get('/attendance/' . $clockInDate->id . "?tid=" . $currentTime->format('Y-m'));

    $restData = Rest::where('attendance_id', $clockInDate->id)->first();
    $paramsDetail = [
      'attendance_clockin'  => "07:55",  // 修正箇所
      'attendance_clockout' => "17:00",
      'rest_id'             => $restData['id'],
      'rest_clockin'        => '10:55',
      'rest_clockout'       => '11:55',
      'rest_id1'            => "",
      'rest_clockin1'       => "",
      'rest_clockout1'      => "",
      'descript'            => "テスト11のため",
      'id'                  => $clockInDate->id,
      'user_id'             => $this->user->id,
      'name'                => $this->user->name,
      'dateline'            => $currentTime->format('Y-m-01'),
      'status'              => 2,
      'restSectMax'         => 1,
      'gardFlg'             => 0,
    ];
    $respose = $this->post('/attendance/' . $clockInDate->id, $paramsDetail);

    // 申請一覧
    $respose = $this->get('/stamp_correction_request/list');
    $respose->assertSee('テスト11');

    $respose = $this->post('/logout');
  }

  /** @test */
  public function 勤怠詳細情報修正機能（一般ユーザー）_「承認済み」に管理者が承認した修正申請が全て表示されている()
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

    // ログイン
    $respose = $this->get('/login');
    $this->actingAs($this->user);

    // 詳細表示
    $respose = $this->get('/attendance/' . $clockInDate->id . "?tid=" . $currentTime->format('Y-m'));

    $restData = Rest::where('attendance_id', $clockInDate->id)->first();
    $paramsDetail = [
      'attendance_clockin'  => "07:55",  // 修正箇所
      'attendance_clockout' => "17:00",
      'rest_id'             => $restData['id'],
      'rest_clockin'        => '10:55',
      'rest_clockout'       => '11:55',
      'rest_id1'            => "",
      'rest_clockin1'       => "",
      'rest_clockout1'      => "",
      'descript'            => "テスト11",
      'id'                  => $clockInDate->id,
      'user_id'             => $this->user->id,
      'name'                => $this->user->name,
      'dateline'            => $currentTime->format('Y-m-01'),
      'status'              => 2,
      'restSectMax'         => 1,
      'gardFlg'             => 0,
    ];
    $respose = $this->post('/attendance/' . $clockInDate->id, $paramsDetail);
    $respose = $this->post('/logout');

    // 管理者でログイン
    $respose = $this->get('/admin/login');
    $admin = Admin::where('id', 1)->first();
    $this->actingAs($admin, 'admin');

    // 申請承認画面
    $reqData = Request_Attendance::where('attendance_id', $clockInDate->id)->first();
    $paramsDetail = [
      'attendance_clockin'  => "07:55",
      'attendance_clockout' => "17:00",
      'rest_id'             => $restData['id'],
      'rest_clockin'        => '10:55',
      'rest_clockout'       => '11:55',
      'rest_id1'            => "",
      'rest_clockin1'       => "",
      'rest_clockout1'      => "",
      'descript'            => "テスト11",
      'id'                  => $clockInDate->id,
      'user_id'             => $this->user->id,
      'name'                => $this->user->name,
      'dateline'            => $currentTime->format('Y-m-01'),
      'status'              => 12,  // 承認待ち
      'restSectMax'         => 1,
      'gardFlg'             => 0,
    ];

    $respose = $this->post('/stamp_correction_request/approve/' . $reqData->id,  $paramsDetail);
    $respose = $this->post('/logout');

    // ログイン
    $respose = $this->get('/login');
    $this->actingAs($this->user);

    // 申請一覧(承認済み)
    $respose = $this->get('/stamp_correction_request/list/11');
    $respose->assertSee('テスト11');

    $respose = $this->post('/logout');
  }

  /** @test */
  public function 勤怠詳細情報修正機能（一般ユーザー）_各申請の「詳細」を押下すると申請詳細画面に遷移する()
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

    // ログイン
    $respose = $this->get('/login');
    $this->actingAs($this->user);

    // 詳細表示
    $respose = $this->get('/attendance/' . $clockInDate->id . "?tid=" . $currentTime->format('Y-m'));

    $restData = Rest::where('attendance_id', $clockInDate->id)->first();
    $paramsDetail = [
      'attendance_clockin'  => "07:55",  // 修正箇所
      'attendance_clockout' => "17:00",
      'rest_id'             => $restData['id'],
      'rest_clockin'        => '10:55',
      'rest_clockout'       => '11:55',
      'rest_id1'            => "",
      'rest_clockin1'       => "",
      'rest_clockout1'      => "",
      'descript'            => "テスト11のため",
      'id'                  => $clockInDate->id,
      'user_id'             => $this->user->id,
      'name'                => $this->user->name,
      'dateline'            => $currentTime->format('Y-m-01'),
      'status'              => 2,
      'restSectMax'         => 1,
      'gardFlg'             => 0,
    ];
    $respose = $this->post('/attendance/' . $clockInDate->id, $paramsDetail);

    // 申請一覧
    $respose = $this->get('/stamp_correction_request/list');
    $respose->assertSee('テスト11');

    // 申請詳細
    $reqData = Request_Attendance::where('attendance_id', $clockInDate->id)->first();
    $respose = $this->get('/stamp_correction_request/' . $reqData->id);
    $respose->assertSee('テスト11');

    $respose = $this->post('/logout');
  }
}
