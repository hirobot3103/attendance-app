<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;
use App\Models\Request_Attendance;
use App\Models\Request_Rest;

class AttendanceDetailController extends Controller
{
    public function detail(Request $request, int $id)
    {
        $attendanceUserName  = User::where('id', Auth::user()->id)->first();

        if ($id == 0) {
            if ($request->has('tid')) {

                // 勤怠未登録の日に既に申請が行われている場合のチェック
                // ->reqstatをセット
                $requested_Attendance = Attendance::where('user_id', Auth::user()->id)->whereDate('clock_in', $request['tid'])->first();

                $statValue = 0;
                if (!empty($requested_Attendance)) {
                    $statValue = $requested_Attendance['status'];
                }

                $reqId = $id;
                $reqDate = $request->tid;
                $reqName = $attendanceUserName->name;
                $reqClockIn = "";
                $reqClockOut = "";
                $reqDescript = "";
                $reqStat = $statValue;

                $attendanceRestDates[] = [
                    'id'            => 0,
                    'attendance_id' => 0,
                    'rest_in' => "",
                    'rest_out' => "",
                ];
            }
        } else {
            $attendanceDetailDates = Attendance::where('id', $id)->first();
            $attendanceRestDates = Rest::where('attendance_id', $attendanceDetailDates->id)->get();

            $reqId = $id;
            $reqDate = $request->tid;
            $reqName = $attendanceUserName->name;
            $reqClockIn = $attendanceDetailDates->clock_in;
            $reqClockOut = $attendanceDetailDates->clock_out;
            $reqDescript = $attendanceDetailDates->descript;
            $reqStat = $attendanceDetailDates->status;
        }
        $dispDetailDates[] = [
            'id' => $reqId,
            'dateline' => $reqDate,
            'name' => $reqName,
            'clock_in' => $reqClockIn,
            'clock_out' => $reqClockOut,
            'descript'  => $reqDescript,
            'status'    => $reqStat,
        ];

        return view('attendance-detail', compact('dispDetailDates', 'attendanceRestDates'));
    }

    public function modify(Request $request, int $id)
    {

        // フォームリクエストのセット
        $dispDetailDates[] = [
            'user_id' => Auth::user()->id,
            'attendance_id' => $id,
            'dateline' => $request->dateline,
            'name' => $request->name,
            'clock_in' => $request->attendance_clockin,
            'clock_out' => $request->attendance_clockout,
            'descript'  => $request['discript'],
            'status'    => $request->status,
        ];
        $maxCount = $request->restSectMax;

        if ($request->rest_clockin <> "" or $request->rest_clockout <> "") {

            $attendanceRestDates[] = [
                'rest_id'               => $request->rest_id,
                'request_attendance_id' => $id,
                'rest_in'               => $request->rest_clockin,
                'rest_out'              => $request->rest_clockout,
            ];
        }
        if ($maxCount > 0) {
            for ($counter = 1; $counter <= $maxCount; $counter++) {
                if ($request['rest_clockin' . $counter] <> "" or $request['rest_clockout' . $counter] <> "") {
                    $attendanceRestDates[] = [
                        'rest_id'  => $request['rest_id' . $counter],
                        'request_attendance_id' => $id,
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

        // $params = [
        //     'user_id'       => $dispDetailDatesMain[0]['user_id'],
        //     'attendance_id' => $dispDetailDatesMain[0]['attendance_id'],
        //     'clock_in'      => $dispDetailDatesMain[0]['clock_in'],
        //     'clock_out'     => $dispDetailDatesMain[0]['clock_out'],
        //     'descript'      => $dispDetailDatesMain[0]['descript'],
        //     'status'        => $dispDetailDatesMain[0]['status'],
        // ];
        // Request_Attendance::create($params);
        // dd($params);

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

        // return redirect('/attendance/list');
    }
}
