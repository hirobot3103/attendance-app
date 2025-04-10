<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
// use App\Models\Request_Attendance;
// use App\Models\Rest;
// use App\Models\Request_Rest;
use App\Models\User;
use Carbon\Carbon;

class AttendanceAdminListController extends Controller
{

    // 通算勤務時間（休憩含む）を取得
    private function getAttendanceTimes($date)
    {
        $startTime = new Carbon($date->clock_in);
        $endTime   = new Carbon($date->clock_out);
        return $startTime->diffInMinutes($endTime);
    }

    // 通算休憩時間を取得
    private function getRestTimes($attendId)
    {
        return DB::table('rests')
            ->where('attendance_id', $attendId)
            ->whereNotNull('rest_out')
            ->sum(DB::raw('TIMESTAMPDIFF(MINUTE, rest_in, rest_out)'));
    }

    // 曜日ごとに割り振られた数値を曜日（文字）へ変換
    private function setDayName($formatdayName)
    {
        $retString = "";
        switch ($formatdayName) {
            case '日曜日':
                $retString = "日";
                break;
            case '月曜日':
                $retString = "月";
                break;
            case '火曜日':
                $retString = "火";
                break;
            case '水曜日':
                $retString = "水";
                break;
            case '木曜日':
                $retString = "木";
                break;
            case '金曜日':
                $retString = "金";
                break;
            case '土曜日':
                $retString = "土";
                break;
        }
        return $retString;
    }

    private function actionMain($targetDate)
    {
        $todayDate = new Carbon($targetDate);
        $monthDate = $todayDate->copy();

        $currentYear       = $todayDate->year;
        $currentMonth      = $todayDate->month;
        $currentDay        = $todayDate->day;
        $currentEndOfMonth = $todayDate->daysInMonth;
        $dateStart = $currentYear . '-' . $currentMonth . '-' . $currentDay . ' 00:00:00';
        $dateEnd = $currentYear . '-' . $currentMonth . '-' . $currentDay . ' 23:59:59';

        // $loginUserId = Auth::user()->id;

        $query = Attendance::whereBetween('clock_in', [$dateStart, $dateEnd]);
        $userAttendanceDatas = $query->orderBy('clock_in', 'asc')->orderBy('id', 'asc')->get();
        $userRestDates = $query->with('rests')->get();
        $dispAttendanceDatas = [];
        $titleBaseDate = new Carbon($todayDate);
        $formatDate = $titleBaseDate->format('Y/m/d');
        $formatDayName = $this->setDayName($titleBaseDate->dayName);
        $titleDate = $formatDate . '(' . $formatDayName . ')';

        $userDatas = User::all();

        if (!empty($userDatas)) {
            foreach ($userDatas as $userInfo) {

                $recordId = 0;
                $recordUserId = $userInfo['id'];
                $recordName = $userInfo['name'];
                $recordDate = $titleBaseDate;
                $recordClockIn = "";
                $recordClockOut = "";
                $recordDiffMin = 0;
                $recordDiffRest = 0;
                $recordDiffTotal = 0;
                if (!empty($userAttendanceDatas)) {

                    // 該当する日に勤怠データあれば、値をセット
                    foreach ($userAttendanceDatas as $userDate) {
                        if ($userInfo['id'] == $userDate['user_id']) {

                            $recordId = $userDate->id;
                            $recordName = $userInfo['name'];
                            $recordDate = $titleBaseDate;
                            $recordClockIn = $userDate['clock_in'] ? date('H:i', strtotime($userDate['clock_in'])) : '';
                            $recordClockOut = $userDate['clock_out'] ? date('H:i', strtotime($userDate['clock_out'])) : '';
                            $recordDiffMin = $this->getAttendanceTimes($userDate);
                            $recordDiffRest = $this->getRestTimes($userDate->id);
                            $recordDiffTotal = $recordDiffMin - $recordDiffRest;
                        }
                    }
                }
                $dispAttendanceDatas[] = [
                    'id'               => $recordId,
                    'user_id'          => $recordUserId,
                    'name'             => $recordName,      // ユーザー名
                    'date'             => $recordDate,      // 日付(Y-m-d)
                    'clock_in'         => $recordClockIn,   // 勤務開始時間
                    'clock_out'        => $recordClockOut,  // 退勤時間
                    'def_attendance'   => $recordDiffMin,   // 通算勤務時間（休憩含む）
                    'def_rest'         => $recordDiffRest,  // 通算休憩時間
                    'total_attendance' => $recordDiffTotal, // 通算勤務時間(休憩を差し引いた時間)
                ];
            }
        }

        // 日付カレンダー関連情報
        $navLinkDate = [
            'baseDay' => $monthDate->format('Y-m-d'),
            'year'      => $currentYear,
            'month'     => $currentMonth,
            'day'       => $currentDay,
            'endDay'    => $currentEndOfMonth,
            'dayname'   => $formatDayName,
        ];

        return view('attendance-admin-list', compact('navLinkDate', 'dispAttendanceDatas'));
    }

    public function index()
    {
        $mode = new Carbon();
        $paramDay = $mode->format('Y-m-d');
        return  $this->actionMain($paramDay);
    }

    public function search(Request $request)
    {
        $mode = new Carbon($request->day__current);

        if ($request->has('day_prev')) {
            $paramDay = $mode->subDay()->format('Y-m-d');
        } elseif ($request->has('day_next')) {
            $paramDay = $mode->addDay()->format('Y-m-d');
        } else {
            $paramDay = $mode->format('Y-m-d');
        }

        return  $this->actionMain($paramDay);
    }
}
