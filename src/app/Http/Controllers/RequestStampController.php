<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\Request_Attendance;
use App\Models\Request_Rest;
use App\Models\User;

// adminかwebかで処理を分岐
class RequestStampController extends Controller
{
    private function actionMain($pageId)
    {
        if (Auth::guard('admin')->check()) {

            if ($pageId == 15) {
                $requestDates = Request_Attendance::where('status', '<>', $pageId)->orderBy('clock_in')->get();
            } else {
                $requestDates = Request_Attendance::where('status', 15)->orderBy('clock_in')->get();
            }
            $requestName  = User::all();
            return view('request-admin-list', compact('requestDates', 'requestName'));
        } else {

            if ($pageId == 15) {
                $requestDates = Request_Attendance::where('user_id', Auth::user()->id)->where('status', '<>', $pageId)->orderBy('clock_in')->get();
            } else {
                $requestDates = Request_Attendance::where('user_id', Auth::user()->id)->where('status', 15)->orderBy('clock_in')->get();
            }
            $requestName  = User::where('id', Auth::user()->id)->first();
            return view('request-user-list', compact('requestDates', 'requestName'));
        }
    }

    public function index()
    {
        return $this->actionMain(15);
    }

    public function reqindex(int $pageId)
    {
        return $this->actionMain($pageId);
    }

    public function detail(int $id)
    {
        // 管理者か一般ユーザーかで処理を分岐
        if (Auth::guard('admin')->check()) {
            $attendanceDetailDates = Request_Attendance::where('id', $id)->first();
            $attendanceUserName  = User::where('id', $attendanceDetailDates['user_id'])->first();
            $attendanceRestDates = Request_Rest::where('attendance_id', $attendanceDetailDates['attendance_id'])->get();

            $reqId = $id;
            $reqDate = date('Y-m-d', strtotime($attendanceDetailDates->clock_in));
            $reqUserId = $attendanceUserName->id;
            $reqName = $attendanceUserName->name;
            $reqClockIn = $attendanceDetailDates->clock_in;
            $reqClockOut = $attendanceDetailDates->clock_out;
            $reqDescript = $attendanceDetailDates->descript;
            $reqStat = $attendanceDetailDates->status;

            $dispDetailDates[] = [
                'id' => $reqId,
                'user_id' => $reqUserId,
                'dateline' => $reqDate,
                'name' => $reqName,
                'clock_in' => $reqClockIn,
                'clock_out' => $reqClockOut,
                'descript'  => $reqDescript,
                'status'    => $reqStat,
                'gardFlg'   => 1,  // adminであることを示す
            ];

            return view('attendance-admin-detail', compact('dispDetailDates', 'attendanceRestDates'));
        } else {
            $attendanceUserName  = User::where('id', Auth::user()->id)->first();
            $attendanceDetailDates = Request_Attendance::where('id', $id)->first();
            $attendanceRestDates = Request_Rest::where('attendance_id', $attendanceDetailDates['attendance_id'])->get();

            $reqId = $id;
            $reqDate = date('Y-m-d', strtotime($attendanceDetailDates->clock_in));
            $reqName = $attendanceUserName->name;
            $reqClockIn = $attendanceDetailDates->clock_in;
            $reqClockOut = $attendanceDetailDates->clock_out;
            $reqDescript = $attendanceDetailDates->descript;
            $reqStat = $attendanceDetailDates->status;

            $dispDetailDates[] = [
                'id' => $reqId,
                'dateline' => $reqDate,
                'name' => $reqName,
                'clock_in' => $reqClockIn,
                'clock_out' => $reqClockOut,
                'descript'  => $reqDescript,
                'status'    => $reqStat,
                'gardFlg'   => 0,
            ];
            return view('attendance-detail', compact('dispDetailDates', 'attendanceRestDates'));
        }
    }

    public function modify(Request $request, int $id)
    {

        // フォームリクエストのセット
        if ($id <> 0) {
            $attendId = $id;
        } else {
            // 新規作成の場合、レコードを作っておく
            $tmpAttendanceData = new Attendance();
            $tmpNewData = $tmpAttendanceData->create(['user_id' => $request->user_id]);
            $attendId = $tmpNewData['id'];
        }
        $dispDetailDates[] = [
            'user_id' => $request->user_id,
            'attendance_id' => $attendId,
            'dateline' => $request->dateline,
            'name' => $request->name,
            'clock_in' => $request->attendance_clockin,
            'clock_out' => $request->attendance_clockout,
            'descript'  => $request['discript'],
            'status'    => $request->status,
        ];
        $maxCount = $request->restSectMax;

        // 休憩データがあれば
        if ($request->rest_clockin <> "" or $request->rest_clockout <> "") {

            if ($request->rest_id <> 0) {
                $restId = $request->rest_id;
            } else {
                // 新規作成の場合、レコードを作っておく
                $tmpRestData = new Rest();
                $tmpNewRestData = $tmpRestData->create(['attendance_id' => $attendId]);
                $restId = $tmpNewRestData['id'];
            }

            $attendanceRestDates[] = [
                'rest_id'               => $restId,
                'request_attendance_id' => $attendId,
                'rest_in'               => $request->rest_clockin,
                'rest_out'              => $request->rest_clockout,
            ];
        }
        if ($maxCount > 0) {
            for ($counter = 1; $counter <= $maxCount; $counter++) {
                $tmpNewRestData = [];
                if ($request['rest_clockin' . $counter] <> "" or $request['rest_clockout' . $counter] <> "") {

                    if ($request['rest_id' . $counter] <> 0) {
                        $restId = $request['rest_id' . $counter];
                    } else {
                        // 新規作成の場合、レコードを作っておく
                        $tmpRestData = new Rest();
                        $tmpNewRestData = $tmpRestData->create(['attendance_id' => $attendId]);
                        $restId = $tmpNewRestData['id'];
                    }

                    $attendanceRestDates[] = [
                        'rest_id'  => $restId,
                        'request_attendance_id' => $attendId,
                        'rest_in'  => $request['rest_clockin' . $counter],
                        'rest_out' => $request['rest_clockout' . $counter],
                    ];
                }
            }
        }

        // 申請用勤怠データへ成形
        $attendanceRestDatesMain = [];
        $dispDetailDatesMain = [];
        foreach ($dispDetailDates as $date) {

            if ($date['clock_in'] <> "") {
                $date['status'] = 11;
                $date['clock_in'] = $date['dateline'] . " " . $date['clock_in'];
            }
            if ($date['clock_out'] <> "") {
                $date['status'] = 12;
                $date['clock_out'] = $date['dateline'] . " " . $date['clock_out'];
            }

            if (!empty($attendanceRestDates)) {
                foreach ($attendanceRestDates as $restDate) {

                    if ($restDate['rest_in'] <> "") {
                        $date['status'] = $date['status'] == 12 ? 12 : 13;
                        $restDate['rest_in'] = $date['dateline'] . " " . $restDate['rest_in'];
                    }
                    if ($restDate['rest_out'] <> "") {
                        $date['status'] = $date['status'] == 13 ? 12 : 11;
                        $restDate['rest_out'] = $date['dateline'] . " " . $restDate['rest_out'];
                    }

                    $attendanceRestDatesMain[] = [
                        'rest_id'  => $restDate['rest_id'],
                        'request_attendance_id' => $restDate['request_attendance_id'],
                        'rest_in'  => $restDate['rest_in'],
                        'rest_out' => $restDate['rest_out'],
                    ];
                }
            }

            $dispDetailDatesMain[] = [
                'user_id' => $date['user_id'],
                'attendance_id' => $date['attendance_id'],
                'clock_in' => $date['clock_in'],
                'clock_out' => $date['clock_out'],
                'descript'  => $date['descript'],
                'status'    => $date['status'],
            ];
        }

        // 申請DBへ登録
        $attendanceInstance = new Request_Attendance();

        $attendanceInstance->user_id = $dispDetailDatesMain[0]['user_id'];
        $attendanceInstance->attendance_id = $dispDetailDatesMain[0]['attendance_id'];
        $attendanceInstance->clock_in      = $dispDetailDatesMain[0]['clock_in'];
        $attendanceInstance->clock_out     = $dispDetailDatesMain[0]['clock_out'];
        $attendanceInstance->descript      = $dispDetailDatesMain[0]['descript'];
        $attendanceInstance->status        = $dispDetailDatesMain[0]['status'];
        $attendanceInstance->save();

        // 該当する勤怠データのステータスを変更
        Attendance::where('id', $dispDetailDatesMain[0]['attendance_id'])->update(['status' => $dispDetailDatesMain[0]['status']]);

        // 休憩関連のデータを保存
        if (!empty($attendanceRestDatesMain)) {
            $params = [];
            $restDate = [];
            foreach ($attendanceRestDatesMain as $restDate) {
                $newId = $restDate['rest_id'] > 0 ? $restDate['rest_id'] : 0;
                $params = [
                    'rest_id'       => $newId,
                    'attendance_id' => $restDate['request_attendance_id'],
                    'rest_in'       => $restDate['rest_in'],
                    'rest_out'      => $restDate['rest_out'],
                ];
                Request_Rest::create($params);
            }
        }

        // 勤怠一覧へレダイレクト
        return redirect('/stamp_correction_request/list');
    }

    public function approve(Request $request, $attendance_correct_request)
    {
        // 修正ボタン押下時
        if ($request->has('admin_btn_mod')) {
            return $this->modify($request, $attendance_correct_request);
        }

        // // 該当勤怠データを更新
        $queryReqAttendance = Request_Attendance::where('id', $attendance_correct_request);
        $requestDate = $queryReqAttendance->first();
        if (!empty($requestDate)) {
            $queryAttendance = Attendance::where('id', $requestDate['attendance_id']);
            $paramsAttenndance = [
                'clock_in' => $request['dateline'] . ' ' . $request['attendance_clockin'],
                'clock_out' => $request['dateline'] . ' ' . $request['attendance_clockout'],
                'status' => 2,
            ];
            $queryAttendance->update($paramsAttenndance);

            $queryReqRest = Request_Rest::where('attendance_id', $requestDate['attendance_id']);
            $requestRestData = $queryReqRest->get();
            if (!empty($requestRestData)) {
                foreach ($requestRestData as $restData) {
                    $queryRest = Rest::where('id', $restData['rest_id']);
                    $paramsRest = [
                        'rest_in' => $restData['rest_in'],
                        'rest_out' => $restData['rest_out'],
                    ];
                    $queryRest->update($paramsRest);
                }
            }

            // 休憩の申請データはあるがDBでは未登録の場合、新規作成


            // 申請を承認済みにする
            $statusValue = 15;
            $queryReqAttendance->update(['status' => $statusValue]);
        }
        // 一覧へリダイレクト
        return redirect(route('admin.dashboard'));
    }
}
