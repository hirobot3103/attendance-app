<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\Request_Attendance;
use App\Models\Request_Rest;
use App\Models\User;
use App\Http\Requests\StaffDetailRequest;
use App\Http\Requests\StaffRestDetailRequest;

class RequestStampController extends Controller
{
    // adminかwebかで処理を分岐
    private function actionMain($pageId)
    {
        if (Auth::guard('admin')->check()) {
            if ($pageId == 15) {
                $requestDates = Request_Attendance::where('status', '<>', $pageId)->orderBy('clock_in')->get();
            } else {
                $requestDates = Request_Attendance::where('status', 15)->orderBy('clock_in')->get();
            }
            $requestName  = User::all();
            return view('request-admin-list', compact('requestDates', 'requestName', 'pageId'));
        } else {
            if ($pageId == 15) {
                $requestDates = Request_Attendance::where('user_id', Auth::user()->id)->where('status', '<>', $pageId)->orderBy('clock_in')->get();
            } else {
                $requestDates = Request_Attendance::where('user_id', Auth::user()->id)->where('status', 15)->orderBy('clock_in')->get();
            }
            $requestName  = User::where('id', Auth::user()->id)->first();
            return view('request-user-list', compact('requestDates', 'requestName', 'pageId'));
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
            $attendanceUserName    = User::where('id', $attendanceDetailDates['user_id'])->first();
            $attendanceRestDates   = Request_Rest::where('attendance_id', $attendanceDetailDates['attendance_id'])
                ->where('req_attendance_id', $id)->get();

            $reqId       = $id;
            $reqDate     = date('Y-m-d', strtotime($attendanceDetailDates->clock_in));
            $reqUserId   = $attendanceUserName->id;
            $reqName     = $attendanceUserName->name;
            $reqClockIn  = $attendanceDetailDates->clock_in;
            $reqClockOut = $attendanceDetailDates->clock_out;
            $reqDescript = $attendanceDetailDates->descript;
            $reqStat     = $attendanceDetailDates->status;

            $dispDetailDates[] = [
                'id'        => $reqId,
                'user_id'   => $reqUserId,
                'dateline'  => $reqDate,
                'name'      => $reqName,
                'clock_in'  => $reqClockIn,
                'clock_out' => $reqClockOut,
                'descript'  => $reqDescript,
                'status'    => $reqStat,
                'gardFlg'   => 1,  // adminであることを示す
            ];
            return view('attendance-admin-detail', compact('dispDetailDates', 'attendanceRestDates'));
        } else {
            $attendanceUserName    = User::where('id', Auth::user()->id)->first();
            $attendanceDetailDates = Request_Attendance::where('id', $id)->first();
            $attendanceRestDates   = Request_Rest::where('attendance_id', $attendanceDetailDates['attendance_id'])
                ->where('req_attendance_id', $id)->get();

            $reqId       = $id;
            $reqDate     = date('Y-m-d', strtotime($attendanceDetailDates->clock_in));
            $reqName     = $attendanceUserName->name;
            $reqClockIn  = $attendanceDetailDates->clock_in;
            $reqClockOut = $attendanceDetailDates->clock_out;
            $reqDescript = $attendanceDetailDates->descript;
            $reqStat     = $attendanceDetailDates->status;

            $dispDetailDates[] = [
                'id'        => $reqId,
                'dateline'  => $reqDate,
                'name'      => $reqName,
                'clock_in'  => $reqClockIn,
                'clock_out' => $reqClockOut,
                'descript'  => $reqDescript,
                'status'    => $reqStat,
                'gardFlg'   => 0,
                'target_id' => $attendanceUserName->id,
                'user_id'   => $attendanceUserName->id,
            ];
            return view('attendance-staff-detail', compact('dispDetailDates', 'attendanceRestDates'));
        }
    }

    public function modify(Request $request, int $id)
    {
        // 空欄かどうか、入力された時刻が不適切かをチェック
        $requestVaridateInstance = new StaffDetailRequest;
        [$inputData, $roles, $messages] = $requestVaridateInstance->varidateModify($request);
        Validator::make($inputData, $roles, $messages)->validate();

        // // 休憩データ同士の関係をチェック
        // $requestRestVaridateInstance = new StaffRestDetailRequest;
        // [$inputData, $roles, $messages] = $requestRestVaridateInstance->varidateRestRelation($inputData);
        // Validator::make($inputData, $roles, $messages)->validate();

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
        return redirect('/stamp_correction_request/list');
    }

    public function approve(Request $request, $attendance_correct_request)
    {
        // 修正ボタン押下時
        if ($request->has('admin_btn_mod')) {
            return $this->modify($request, $attendance_correct_request);
        }

        // 該当勤怠データを更新
        $queryReqAttendance = Request_Attendance::where('id', $attendance_correct_request)->orderBy('updated_at', 'DESC');
        $requestDate = $queryReqAttendance->first();

        if (!empty($requestDate)) {
            $queryAttendance = Attendance::where('id', $requestDate['attendance_id']);
            $paramsAttenndance = [
                'descript'  => $request['descript'],
                'clock_in'  => $request['dateline'] . ' ' . $request['attendance_clockin'],
                'clock_out' => $request['dateline'] . ' ' . $request['attendance_clockout'],
                'status'    => 2,
            ];
            $queryAttendance->update($paramsAttenndance);

            $queryReqRest = Request_Rest::where('attendance_id', $requestDate['attendance_id'])
                ->where('req_attendance_id', $requestDate['id']);
            $requestRestData = $queryReqRest->get();

            // 休暇データを更新する
            // ＊ restsテーブルにデータがあって、request_restsにはない場合、削除
            if (!empty($requestRestData)) {

                // restsテーブル（現状：申請が適用される前）を取得
                $currentQueryInstance = new Rest();
                $currentRestQuery = $currentQueryInstance::where('attendance_id', $requestDate['attendance_id']);
                $currentRestDatas = $currentRestQuery->get();
                foreach ($currentRestDatas as $currentRestData) {
                    $currentRestId = $currentRestData['id'];
                    $QueryInstancerequest = new Request_Rest();
                    $requestRestData = $QueryInstancerequest::where('attendance_id', $requestDate['attendance_id'])
                        ->where('req_attendance_id', $requestDate['id'])
                        ->where('rest_id', $currentRestId)->first();

                    // 現状の休暇データが申請で更新される対象かどうか
                    if (!empty($requestRestData)) {

                        // 更新対象（現状で休暇データがあり、申請側にもある）
                        $paramsRest = [
                            'rest_in'  => $requestRestData['rest_in'],
                            'rest_out' => $requestRestData['rest_out'],
                        ];
                        $currentQueryInstance = new Rest();
                        $currentQueryInstance::where('id', $currentRestId)->update($paramsRest);
                    } else {

                        // 削除対象（現状では休暇データがあるが申請側にはない）
                        $currentQueryInstance = new Rest();
                        $currentQueryInstance::where('id', $currentRestId)->delete();
                    }
                }
            }

            // 申請を承認済みにする
            $statusValue = 15;
            $queryReqAttendance->update(['status' => $statusValue]);
        }

        // 一覧へリダイレクト
        return redirect(route('admin.dashboard'));
    }
}
