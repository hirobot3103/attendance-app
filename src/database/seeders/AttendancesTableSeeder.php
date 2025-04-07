<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendancesTableSeeder extends Seeder
{
    public function run(): void
    {
        // 5人分の勤怠データ作成
        $maxId = 5;

        // 実行時点の月と過去2か月のデータを作成
        $currentDate = new Carbon();
        $currentYear       = $currentDate->year;
        $currentMonth      = $currentDate->month;
        $currentEndOfMonth = $currentDate->day;  // 実行時点を最終とする
        $this->makeAttendanceCurrentMonthData($maxId, $currentYear, $currentMonth, $currentEndOfMonth);

        $oneMonthAgoBaseDate = new Carbon();
        $oneMonthAgoDate       = $oneMonthAgoBaseDate->subMonth(1);
        $oneMonthAgoYear       = $oneMonthAgoDate->year;
        $oneMonthAgoMonth      = $oneMonthAgoDate->month;
        $oneMonthAgoEndOfMonth = $oneMonthAgoDate->daysInMonth;
        $this->makeAttendanceData($maxId, $oneMonthAgoYear, $oneMonthAgoMonth, $oneMonthAgoEndOfMonth);

        $twoMonthAgoBaseDate = new Carbon();
        $twoMonthAgoDate       = $twoMonthAgoBaseDate->subMonth(2);
        $twoMonthAgoYear       = $twoMonthAgoDate->year;
        $twoMonthAgoMonth      = $twoMonthAgoDate->month;
        $twoMonthAgoEndOfMonth = $twoMonthAgoDate->daysInMonth;
        $this->makeAttendanceData($maxId, $twoMonthAgoYear, $twoMonthAgoMonth, $twoMonthAgoEndOfMonth);

        // 5人分の勤怠データに対応した申請データを作成
        $this->makeAttendanceRequestData();
    }


    // 実行時点での当月
    private function makeAttendanceCurrentMonthData(int $maxId, $year, $month, $endOfMonth)
    {
        $stats = [2, 2, 2, 2, 12, 2, 2, 12, 2, 15, 2, 2, 2, 2, 2, 2, 12, 2, 2, 2, 2, 12, 15];
        $descripts = [
            "早退：保育園へ迎えに行くため",
            "早退：体調不良のため",
            "早退：そのほか",
            "遅刻：電車事故による遅延",
            "遅刻：通院のため",
            "遅刻：保育園に送りにいったため",
            "遅刻：そのほか",
            "その他：打刻操作ミス",
            "その他：休憩時間の打刻忘れ",
            "その他：勤怠管理アプリの不具合のため",
        ];

        // 各月 土日を除く1日～月末日までId毎に作成
        for ($nowId = 1; $nowId <= $maxId; $nowId++) {

            // 実行時点当月（実行時点当日は、就業前とする）
            for ($dayCount = 1; $dayCount < $endOfMonth; $dayCount++) {

                $strDate = $year . '-' . $month . '-' . $dayCount;
                $paramDay = new Carbon($strDate);
                $starttime = $strDate . ' ' . $this->makeClockInOunt(1);
                $endtime   = $strDate . ' ' . $this->makeClockInOunt(2);
                $randId = rand(0, count($stats) - 1);
                $datastatus = $stats[$randId];
                $descript = "";

                // ステータスが承認済みの場合、申請理由と、その理由に合わせて時刻を作成
                if ($datastatus == 15) {
                    $randId = rand(0, count($descripts) - 1);
                    $descript = $descripts[$randId];
                    if (preg_match("/^早退*/", $descript, $matches)) {
                        $endtime   = $strDate . ' ' . $this->makeClockInOunt(4);
                    }
                    if (preg_match("/^遅刻*/", $descript, $matches)) {
                        $starttime   = $strDate . ' ' . $this->makeClockInOunt(3);
                    }
                }

                // 土日でなければデータを作成
                if (($paramDay->isSaturday() == false) and ($paramDay->isSunday() == false)) {

                    if ($descript <> "") {
                        $params[] = [
                            'user_id'   => $nowId,
                            'clock_in'  => $starttime,
                            'clock_out' => $endtime,
                            'status'    => $datastatus,
                            'descript' => $descript,
                        ];
                    } else {
                        $params[] = [
                            'user_id'   => $nowId,
                            'clock_in'  => $starttime,
                            'clock_out' => $endtime,
                            'status'    => $datastatus,
                            'descript' => null,
                        ];
                    }
                }
            }
        }
        // DBへ登録
        DB::table('attendances')->insert($params);
    }

    private function makeAttendanceData(int $maxId, $year, $month, $endOfMonth)
    {
        $stats = [2, 2, 2, 2, 12, 2, 2, 12, 2, 15, 2, 2, 2, 2, 2, 2, 12, 2, 2, 2, 2, 12, 15];
        $descripts = [
            "早退：保育園へ迎えに行くため",
            "早退：体調不良のため",
            "早退：そのほか",
            "遅刻：電車事故による遅延",
            "遅刻：通院のため",
            "遅刻：保育園に送りにいったため",
            "遅刻：そのほか",
            "その他：打刻操作ミス",
            "その他：休憩時間の打刻忘れ",
            "その他：勤怠管理アプリの不具合のため",
        ];

        // 各月 土日を除く1日～月末日までId毎に作成
        for ($nowId = 1; $nowId <= $maxId; $nowId++) {
            for ($dayCount = 1; $dayCount <= $endOfMonth; $dayCount++) {

                $strDate = $year . '-' . $month . '-' . $dayCount;
                $paramDay = new Carbon($strDate);
                $starttime = $strDate . ' ' . $this->makeClockInOunt(1);
                $endtime   = $strDate . ' ' . $this->makeClockInOunt(2);
                $randId = rand(0, count($stats) - 1);
                $datastatus = $stats[$randId];
                $descript = "";

                // ステータスが承認済みの場合、申請理由と、その理由に合わせて時刻を作成
                if ($datastatus == 15) {
                    $randId = rand(0, count($descripts) - 1);
                    $descript = $descripts[$randId];
                    if (preg_match("/^早退*/", $descript, $matches)) {
                        $endtime   = $strDate . ' ' . $this->makeClockInOunt(4);
                    }
                    if (preg_match("/^遅刻*/", $descript, $matches)) {
                        $starttime   = $strDate . ' ' . $this->makeClockInOunt(3);
                    }
                }

                // 土日でなければデータを作成
                if (($paramDay->isSaturday() == false) and ($paramDay->isSunday() == false)) {

                    if ($descript <> "") {
                        $params[] = [
                            'user_id'   => $nowId,
                            'clock_in'  => $starttime,
                            'clock_out' => $endtime,
                            'status'    => $datastatus,
                            'descript' => $descript,
                        ];
                    } else {
                        $params[] = [
                            'user_id'   => $nowId,
                            'clock_in'  => $starttime,
                            'clock_out' => $endtime,
                            'status'    => $datastatus,
                            'descript' => null,
                        ];
                    }
                }
            }
        }
        // DBへ登録
        DB::table('attendances')->insert($params);
    }

    private function makeClockInOunt(int $modeInOut)
    {
        $rtnHour = "";
        $rtnMin = "";

        switch ($modeInOut) {
            case 1: // 通常の出勤時間
                $rtnHour = (string) rand(6, 7);
                $rtnMin = (string) rand(0, 59);
                break;

            case 2: // 通常の退勤時間
                $rtnHour = (string) rand(17, 18);
                $rtnMin = (string) rand(0, 59);
                break;

            case 3: // 遅刻の出勤時間
                $rtnHour = (string) rand(9, 13);
                $rtnMin = (string) rand(0, 59);
                break;

            case 4: // 早退の退勤時間
                $rtnHour = (string) rand(10, 16);
                $rtnMin = (string) rand(0, 59);
                break;

            default:
                $rtnHour = "0";
                $rtnMin = "00";
        }
        return $rtnHour . ':' . $rtnMin . ':00';
    }

    private function makeAttendanceRequestData()
    {
        $attendanceDatas = DB::table('attendances')->where('status', 12)->orwhere('status', 15)->orderBy('user_id')->get();

        $params = [];
        foreach ($attendanceDatas as $rowData) {
            if ($rowData->status == 12) {

                $descripts = [
                    "早退：保育園へ迎えに行くため",
                    "早退：体調不良のため",
                    "早退：そのほか",
                    "遅刻：電車事故による遅延",
                    "遅刻：通院のため",
                    "遅刻：保育園に送りにいったため",
                    "遅刻：そのほか",
                    "その他：打刻操作ミス",
                    "その他：休憩時間の打刻忘れ",
                    "その他：勤怠管理アプリの不具合のため",
                ];

                $paramDay = new Carbon($rowData->clock_in);
                $randId = rand(0, count($descripts) - 1);
                $descript = $descripts[$randId];
                $startTime = $rowData->clock_in;
                $endTime = $rowData->clock_out;
                if (preg_match("/^早退*/", $descript, $matches)) {
                    $endTime   = $paramDay->format('Y-m-d') . ' ' . $this->makeClockInOunt(4);
                }
                if (preg_match("/^遅刻*/", $descript, $matches)) {
                    $startTime   = $paramDay->format('Y-m-d')  . ' ' . $this->makeClockInOunt(3);
                }

                $params[] = [
                    'user_id'       => $rowData->user_id,
                    'attendance_id' => $rowData->id,
                    'clock_in'      => $startTime,
                    'clock_out'     => $endTime,
                    'status'        => $rowData->status,
                    'descript'      => $descript,
                ];
            } elseif ($rowData->status == 15) {
                $params[] = [
                    'user_id'       => $rowData->user_id,
                    'attendance_id' => $rowData->id,
                    'clock_in'      => $rowData->clock_in,
                    'clock_out'     => $rowData->clock_out,
                    'status'        => $rowData->status,
                    'descript'      => $rowData->descript,
                ];
            }
        }
        DB::table('request_attendances')->insert($params);
    }
}
