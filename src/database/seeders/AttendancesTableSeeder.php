<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendancesTableSeeder extends Seeder
{
    public function run(): void
    {
        // user5人分の勤怠データを作成
        $this->makeAttendanceBaseDate(5);
    }

    function makeAttendanceBaseDate(int $maxId)
    {
        // 実行時点の月と過去2か月のデータを作成
        $currentDate = new Carbon();
        $currentYear       = $currentDate->year;
        $currentMonth      = $currentDate->month;
        $currentEndOfMonth = $currentDate->day;  // 実行時点を最終とする

        $oneMonthAgoBaseDate = new Carbon();
        $oneMonthAgoDate       = $oneMonthAgoBaseDate->subMonth(1);
        $oneMonthAgoYear       = $oneMonthAgoDate->year;
        $oneMonthAgoMonth      = $oneMonthAgoDate->month;
        $oneMonthAgoEndOfMonth = $oneMonthAgoDate->daysInMonth;

        $twoMonthAgoBaseDate = new Carbon();
        $twoMonthAgoDate       = $twoMonthAgoBaseDate->subMonth(2);
        $twoMonthAgoYear       = $twoMonthAgoDate->year;
        $twoMonthAgoMonth      = $twoMonthAgoDate->month;
        $twoMonthAgoEndOfMonth = $twoMonthAgoDate->daysInMonth;

        $params = [];
        $stats = [2, 2, 2, 2, 12, 2, 2, 12, 2, 2, 2, 2, 2, 13, 2, 2, 2, 2, 12, 12, 2];

        // 各月 土日を除く1日～月末日までId毎に作成
        for ($nowId = 1; $nowId <= $maxId; $nowId++) {

            // 実行時点当月（実行時点当日は、就業前とする）
            for ($dayCount = 1; $dayCount < $currentEndOfMonth; $dayCount++) {

                $strDate = $currentYear . '-' . $currentMonth . '-' . $dayCount;
                $paramDay = new Carbon($strDate);
                $starttime = $strDate . ' ' . '8:00:00';
                $endtime   = $strDate . ' ' . '17:00:00';
                $randId = rand(0, count($stats) - 1);
                $datastatus = $stats[$randId];

                // 土日でなければデータを作成
                if (($paramDay->isSaturday() == false) and ($paramDay->isSunday() == false)) {

                    $params[] = [
                        'user_id'   => $nowId,
                        'clock_in'  => $starttime,
                        'clock_out' => $endtime,
                        'status'    => $datastatus,
                    ];
                }
            }
            // DBへ登録
            DB::table('attendances')->insert($params);

            // 前月
            $params = [];
            for ($dayCount = 1; $dayCount <= $oneMonthAgoEndOfMonth; $dayCount++) {

                $strDate = $oneMonthAgoYear . '-' . $oneMonthAgoMonth . '-' . $dayCount;
                $paramDay = new Carbon($strDate);
                $starttime = $strDate . ' ' . '8:00:00';
                $endtime   = $strDate . ' ' . '17:00:00';
                $randId = rand(0, count($stats) - 1);
                $datastatus = $stats[$randId];

                // 土日でなければデータを作成
                if (($paramDay->isSaturday() == false) and ($paramDay->isSunday() == false)) {

                    $params[] = [
                        'user_id'   => $nowId,
                        'clock_in'  => $starttime,
                        'clock_out' => $endtime,
                        'status'    => $datastatus,
                    ];
                }
            }
            // DBへ登録
            DB::table('attendances')->insert($params);

            // 前々月
            $params = [];
            for ($dayCount = 1; $dayCount <= $twoMonthAgoEndOfMonth; $dayCount++) {

                $strDate = $twoMonthAgoYear . '-' . $twoMonthAgoMonth . '-' . $dayCount;
                $paramDay = new Carbon($strDate);
                $starttime = $strDate . ' ' . '8:00:00';
                $endtime   = $strDate . ' ' . '17:00:00';
                $randId = rand(0, count($stats) - 1);
                $datastatus = $stats[$randId];

                // 土日でなければデータを作成
                if (($paramDay->isSaturday() == false) and ($paramDay->isSunday() == false)) {

                    $params[] = [
                        'user_id'   => $nowId,
                        'clock_in'  => $starttime,
                        'clock_out' => $endtime,
                        'status'    => $datastatus,
                    ];
                }
            }
            DB::table('attendances')->insert($params);
        }
    }
}
