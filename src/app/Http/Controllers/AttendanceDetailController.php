<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;
use App\Models\Request_Attendance;
use App\Models\Request_Rest;
use App\Http\Requests\StaffDetailRequest;

class AttendanceDetailController extends Controller
{
    public function detail(Request $request, int $id)
    {
        $attendanceUserName  = User::where('id', Auth::user()->id)->first();

        if ($id <> 0) {
            $attendanceDetailDates = Attendance::where('id', $id)->first();
            $attendanceRestDates = Rest::where('attendance_id', $attendanceDetailDates['id'])->get();
            $reqId = $id;
            $reqUserId = Auth::user()->id;
            $reqDate = date('Y-m-d', strtotime($attendanceDetailDates->clock_in));
            $reqName = $attendanceUserName->name;
            $reqClockIn = $attendanceDetailDates->clock_in;
            $reqClockOut = $attendanceDetailDates->clock_out;
            $reqDescript = $attendanceDetailDates->descript;
            $reqStat = $attendanceDetailDates->status;
        } else {
            $reqDate = date('Y-m-d', strtotime($request->tid));
            $startTime = $request->tid . ' 00:00:00';
            $endTime   = $request->tid . ' 23:59:59';
            $queryReq = Request_Attendance::whereBetween('clock_in', [$startTime, $endTime])
                ->where('user_id', $attendanceUserName['id']);
            $userAttendanceDatas = $queryReq->orderBy('clock_in', 'asc')->first();
            if (empty($userAttendanceDatas)) {
                $reqId = $id;
                $reqClockIn = "";
                $reqClockOut = "";
                $reqStat = 14;  // 新規追加申請

            } else {
                $reqId = $userAttendanceDatas['id'];
                $reqClockIn = "";
                $reqClockOut = "";
                $reqStat = $userAttendanceDatas['status'];
            }
            $reqUserId = Auth::user()->id;
            $reqName = $attendanceUserName->name;
            $reqDescript = "";

            $attendanceRestDates = [];
        }

        $dispDetailDates[] = [
            'id' => $reqId,
            'user_id' => $reqUserId,
            'target_id' => $reqUserId,
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

    public function modify(Request $request, int $id)
    {
        // 空欄かどうか、入力された時刻が不適切かをチェック
        $requestVaridateInstance = new StaffDetailRequest;
        [$inputData, $roles, $messages] = $requestVaridateInstance->varidateModify($request);
        Validator::make($inputData, $roles, $messages)->validate();

        if ($id <> 0) {
            $attendId = $id;
        } else {

            // 新規作成の場合、レコードを作っておく
            $tmpAttendanceData = new Attendance();
            $tmpNewData        = $tmpAttendanceData->create(['user_id' => $request->user_id]);
            $attendId          = $tmpNewData['id'];
        }
        $dispDetailDates[] = [
            'user_id'       => $request->user_id,
            'attendance_id' => $attendId,
            'dateline'      => $request->dateline,
            'name'          => $request->name,
            'clock_in'      => $request->attendance_clockin,
            'clock_out'     => $request->attendance_clockout,
            'descript'      => $request->descript,
            'status'        => $request->status,
        ];
        $maxCount = $request->restSectMax;

        if ($request->rest_clockin <> "" || $request->rest_clockout <> "") {

            if ($request->rest_id <> 0) {
                $restId = $request->rest_id;
            } else {
                $tmpRestData    = new Rest();
                $tmpNewRestData = $tmpRestData->create(['attendance_id' => $attendId]);
                $restId         = $tmpNewRestData['id'];
            }

            $attendanceRestDates[] = [
                'rest_id'               => $restId,
                'request_attendance_id' => $attendId,
                'rest_in'               => $request->rest_clockin,
                'rest_out'              => $request->rest_clockout,
            ];
        }

        // 休憩時間が２つ以上の場合
        if ($maxCount > 0) {
            for ($counter = 1; $counter <= $maxCount; $counter++) {
                $tmpNewRestData = [];
                if ($request['rest_clockin' . $counter] <> "" || $request['rest_clockout' . $counter] <> "") {

                    if ($request['rest_id' . $counter] <> 0) {
                        $restId = $request['rest_id' . $counter];
                    } else {
                        $tmpRestData = new Rest();
                        $tmpNewRestData = $tmpRestData->create(['attendance_id' => $attendId]);
                        $restId = $tmpNewRestData['id'];
                    }

                    $attendanceRestDates[] = [
                        'rest_id'               => $restId,
                        'request_attendance_id' => $attendId,
                        'rest_in'               => $request['rest_clockin' . $counter],
                        'rest_out'              => $request['rest_clockout' . $counter],
                    ];
                }
            }
        }

        // 申請用勤怠データへ成形
        $attendanceRestDatesMain = [];
        $dispDetailDatesMain = [];
        foreach ($dispDetailDates as $date) {

            $date['status'] = 12;

            if ($date['clock_in'] <> "") {
                $date['clock_in'] = $date['dateline'] . " " . $date['clock_in'];
            }
            if ($date['clock_out'] <> "") {
                $date['clock_out'] = $date['dateline'] . " " . $date['clock_out'];
            }

            if (!empty($attendanceRestDates)) {
                foreach ($attendanceRestDates as $restDate) {
                    $date['status'] = 12;

                    if ($restDate['rest_in'] <> "") {
                        $restDate['rest_in'] = $date['dateline'] . " " . $restDate['rest_in'];
                    }
                    if ($restDate['rest_out'] <> "") {
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
        $newParam = [
            'user_id'       => $dispDetailDatesMain[0]['user_id'],
            'attendance_id' => $dispDetailDatesMain[0]['attendance_id'],
            'clock_in'      => $dispDetailDatesMain[0]['clock_in'],
            'clock_out'     => $dispDetailDatesMain[0]['clock_out'],
            'descript'      => $dispDetailDatesMain[0]['descript'],
            'status'        => $dispDetailDatesMain[0]['status'],
        ];
        $newRequestAttendance = Request_Attendance::create($newParam);

        // 該当する勤怠データのステータスを申請中に変更
        Attendance::where('id', $dispDetailDatesMain[0]['attendance_id'])->update(['status' => $dispDetailDatesMain[0]['status']]);

        // 休憩関連の申請データを作成
        if (!empty($attendanceRestDatesMain)) {
            $restDate = [];
            $params   = [];
            foreach ($attendanceRestDatesMain as $restDate) {
                $newId  = $restDate['rest_id'] > 0 ? $restDate['rest_id'] : 0;
                $params = [
                    'rest_id'       => $newId,
                    'req_attendance_id' => $newRequestAttendance->id,
                    'attendance_id' => $restDate['request_attendance_id'],
                    'rest_in'       => $restDate['rest_in'],
                    'rest_out'      => $restDate['rest_out'],
                ];
                Request_Rest::create($params);
            }
        }

        // 勤怠一覧へレダイレクト
        return redirect('/attendance/list');
    }
}
