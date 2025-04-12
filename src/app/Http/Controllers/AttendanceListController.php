<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceListController extends Controller
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
        $currentEndOfMonth = $todayDate->daysInMonth;
        $dateStart = $currentYear . '-' . $currentMonth . '-01 00:00:00';
        $dateEnd = $currentYear . '-' . $currentMonth . '-' . $currentEndOfMonth . ' 23:59:59';

        $loginUserId = Auth::user()->id;

        $query = Attendance::where('user_id', $loginUserId)->whereBetween('clock_in', [$dateStart, $dateEnd]);
        $userAttendanceDatas = $query->orderBy('clock_in', 'asc')->get();
        $userRestDates = $query->with('rests')->get();

        $dispAttendanceDatas = [];
        $titleDate = [];
        $titleBaseDate = new Carbon($dateStart);
        for ($loop = 1; $loop <= $currentEndOfMonth; $loop++) {
            $formatDate = $titleBaseDate->format('m/d');
            $formatDayName = $this->setDayName($titleBaseDate->dayName);
            $titleDate[] = $formatDate . '(' . $formatDayName . ')';
            $titleBaseDate->addDay();
        }

        foreach ($titleDate as $monthDayDate) {

            // 表示する勤怠データのデフォルト値をセット
            $recordId = 0;
            $recordDate = $monthDayDate;
            $recordClockIn = '';
            $recordClockOut = '';
            $recordDiffMin = '';
            $recordDiffRest = '';
            $recordDiffTotal = "";

            if (!empty($userAttendanceDatas)) {

                // 該当する日に勤怠データあれば、値をセット
                foreach ($userAttendanceDatas as $userDate) {
                    if (substr($monthDayDate, 0, 5) == date('m/d', strtotime($userDate['clock_in']))) {

                        $recordId = $userDate->id;
                        $recordDate = $monthDayDate;
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
                'date' => $recordDate,
                'clock_in' => $recordClockIn,
                'clock_out' => $recordClockOut,
                'def_attendance' => $recordDiffMin,
                'def_rest'       => $recordDiffRest,
                'total_attendance' => $recordDiffTotal,
            ];
        }

        $navLinkDate = [
            'baseMonth' => $monthDate->format('Y-m'),
            'year'      => $currentYear,
            'month'     => $currentMonth,
            'endDay'    => $currentEndOfMonth,
            'target_id' => $loginUserId,
            'user_id' => $loginUserId,
        ];

        return view('attendance-list', compact('navLinkDate', 'dispAttendanceDatas'));
    }

    public function index()
    {
        $mode = new Carbon();
        $paramMonth = $mode->format('Y-m-01');
        return  $this->actionMain($paramMonth);
    }

    public function search(Request $request)
    {
        $mode = new Carbon($request->month__current);

        if ($request->has('month_prev')) {
            $paramMonth = $mode->subMonth()->format('Y-m-01');
        } elseif ($request->has('month_next')) {
            $paramMonth = $mode->addMonth()->format('Y-m-01');
        } else {
            $paramMonth = $mode->format('Y-m-01');
        }

        // 日付($request->month__current)が未来なら、現時点の日付を表示
        // inputtype:カレンダーの場合
        $nowBaseTime = new Carbon();
        $nowTime = $nowBaseTime->format('Y-m-01');
        $inputTime = $mode->format('Y-m-01');

        if ($nowTime < ($inputTime)) {
            $paramMonth = $nowTime;
        }
        return  $this->actionMain($paramMonth);
    }
}
