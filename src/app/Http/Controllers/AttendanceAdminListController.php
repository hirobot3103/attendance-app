<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;
use Carbon\Carbon;

class AttendanceAdminListController extends Controller
{

    private function getAttendanceTimes($date)
    {
        $startTime = new Carbon($date->clock_in);
        $endTime   = new Carbon($date->clock_out);
        return $startTime->diffInMinutes($endTime);
    }

    private function getRestTimes($attendId)
    {
        return DB::table('rests')
            ->where('attendance_id', $attendId)
            ->whereNotNull('rest_out')
            ->sum(DB::raw('TIMESTAMPDIFF(MINUTE, rest_in, rest_out)'));
    }

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

        $loginUserId = Auth::user()->id;

        // dd($dateStart);
        $query = Attendance::whereBetween('clock_in', [$dateStart, $dateEnd]);
        $userAttendanceDatas = $query->orderBy('clock_in', 'asc')->get();
        $userRestDates = $query->with('rests')->get();

        // dd($userAttendanceDatas);

        $dispAttendanceDatas = [];
        $titleBaseDate = new Carbon($todayDate);
        $formatDate = $titleBaseDate->format('Y/m/d');
        $formatDayName = $this->setDayName($titleBaseDate->dayName);
        $titleDate = $formatDate . '(' . $formatDayName . ')';

        $userDatas = User::all();

        if (!empty($userDatas)) {
            foreach ($userDatas as $userInfo) {

                $recordId = 0;
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
                    'id' => $recordId,
                    'name' => $recordName,
                    'date' => $recordDate,
                    'clock_in' => $recordClockIn,
                    'clock_out' => $recordClockOut,
                    'def_attendance' => $recordDiffMin,
                    'def_rest'       => $recordDiffRest,
                    'total_attendance' => $recordDiffTotal,
                ];
            }
        }

        $navLinkDate = [
            'baseDay' => $monthDate->format('Y-m-d'),
            'year'      => $currentYear,
            'month'     => $currentMonth,
            'day'       => $currentDay,
            'endDay'    => $currentEndOfMonth,
            'dayname'   => $formatDayName,
        ];

        // データ取得までOK
        dd($dispAttendanceDatas);
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
            $paramDay = $mode->format('Y-m-01');
        }

        return  $this->actionMain($paramDay);
    }
}
