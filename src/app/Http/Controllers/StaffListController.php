<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\Request_Attendance;
use Carbon\Carbon;

class StaffListController extends Controller
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

    private function export($linkData, $csvData)
    {
        $dateBaseData = $linkData['year'] . '/';
        $csvFileName = 'attendance_userid' . $csvData[0]['target_id'] . '_' . $linkData['year'] . $linkData['month'] . '.csv';
        $csvHeader = [
            "id",                // 勤怠DBに割り振られたid(勤務なし = 0)
            "date",              // 日付
            "target_id",         // 一般ユーザーのID
            "user_name",         // 一般ユーザー名
            "clock_in",          // 勤務開始時間
            "clock_out",         // 勤務終了時間
            "def_attendance",    // 勤務時間（休憩考慮なし）
            "def_rest",          // 休憩時間通算
            "total_attendance",  // 勤務時間（休憩考慮あり）
        ];

        $response = new StreamedResponse(function () use ($csvHeader, $dateBaseData, $csvData) {
            $createCsvFile = fopen('php://output', 'w');

            mb_convert_variables('SJIS-win', 'UTF-8', $csvHeader);

            fputcsv($createCsvFile, $csvHeader);

            foreach ($csvData as $csv) {

                $csv['date'] = $dateBaseData . $csv['date'];
                fputcsv($createCsvFile, $csv);
            }

            fclose($createCsvFile);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename= "' . $csvFileName . '"',
        ]);

        return $response;
    }

    // 勤怠・休憩データ作成共通処理
    private function actionMain($targetDate, $id, int $csvOutput = 0)
    {
        $todayDate = new Carbon($targetDate);
        $monthDate = $todayDate->copy();

        $currentYear       = $todayDate->year;
        $currentMonth      = $todayDate->month;
        $currentEndOfMonth = $todayDate->daysInMonth;
        $dateStart = $currentYear . '-' . $currentMonth . '-01 00:00:00';
        $dateEnd = $currentYear . '-' . $currentMonth . '-' . $currentEndOfMonth . ' 23:59:59';

        $loginUserId = $id;
        $userName = User::where('id', $id)->first();

        $query = Attendance::where('user_id', $loginUserId)->whereBetween('clock_in', [$dateStart, $dateEnd]);
        $userAttendanceDatas = $query->orderBy('clock_in', 'asc')->get();
        $userRestDates = $query->with('rests')->get();

        // 勤怠データ作成
        $dispAttendanceDatas = [];

        // 日付部分
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
            $recordUserName  = $userName['name'];

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
                'target_id' => $loginUserId,
                'user_name' => $recordUserName,
                'clock_in' => $recordClockIn,
                'clock_out' => $recordClockOut,
                'def_attendance' => $recordDiffMin,
                'def_rest'       => $recordDiffRest,
                'total_attendance' => $recordDiffTotal,
            ];
        }

        // 表示のための年月日、月末データ
        $navLinkDate = [
            'baseMonth' => $monthDate->format('Y-m'),
            'year'      => $currentYear,
            'month'     => $currentMonth,
            'endDay'    => $currentEndOfMonth,
        ];

        // CSV出力ボタン押下時にcsvファイルを作成して返す
        if ($csvOutput == 1) {
            return $this::export($navLinkDate, $dispAttendanceDatas);
        }
        return view('attendance-staff-list', compact('navLinkDate', 'dispAttendanceDatas'));
    }

    public function index()
    {
        $userDates = User::all();
        return view('staff-list', compact('userDates'));
    }

    public function list($id)
    {
        $mode = new Carbon();
        $paramMonth = $mode->format('Y-m-01');
        return  $this->actionMain($paramMonth, $id);
    }

    public function search(Request $request, $id)
    {
        $mode = new Carbon($request->month__current);
        $paramId = $id;

        if ($request->has('month_prev')) {
            $paramMonth = $mode->subMonth()->format('Y-m-01');
        } elseif ($request->has('month_next')) {
            $paramMonth = $mode->addMonth()->format('Y-m-01');
        } else {
            $paramMonth = $mode->format('Y-m-01');
        }

        $csvOutPut = 0;
        if ($request->has('csv_btn')) {
            $csvOutPut = 1;
        }

        return  $this->actionMain($paramMonth, $paramId, $csvOutPut);
    }

    public function detail(Request $request, int $id)
    {
        // バリデーション（休憩の項目数は可変)

        if ($request->has('uid')) {
            $attendanceUserName  = User::where('id', $request->uid)->first();
        } elseif ($request->has('user_id')) {
            $attendanceUserName  = User::where('id', $request->user_id)->first();
        } else {

            // バリデーションでエラーがあった後、GETで呼び出される
            $dispDetailDates[] = [];
            $attendanceRestDates = [];
            return view('attendance-staff-detail', compact('dispDetailDates', 'attendanceRestDates'));
        }

        if ($id <> 0) {
            $attendanceDetailDates = Attendance::where('id', $id)->first();
            $attendanceRestDates = Rest::where('attendance_id', $attendanceDetailDates['id'])->get();
            $reqId = $id;
            $reqUserId = $request->uid;
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
            $reqUserId = $request->uid;
            $reqName = $attendanceUserName->name;
            $reqDescript = "";

            $attendanceRestDates = [];
        }

        $dispDetailDates[] = [
            'id' => $reqId,
            'target_id' => $reqUserId,
            'dateline' => $reqDate,
            'name' => $reqName,
            'clock_in' => $reqClockIn,
            'clock_out' => $reqClockOut,
            'descript'  => $reqDescript,
            'status'    => $reqStat,
            'gardFlg'   => 1,
        ];
        return view('attendance-staff-detail', compact('dispDetailDates', 'attendanceRestDates'));
    }
}
