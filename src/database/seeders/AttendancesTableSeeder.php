<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendancesTableSeeder extends Seeder
{
    public function run(): void
    {
        // user5人分の勤怠データの土台を作成
        $this->makeAttendanceBaseDate(5);

        // 申請済み、申請中のデータを作成

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

        // 各月 土日を除く1日～月末日までId毎に作成
        for ($nowId = 1; $nowId <= $maxId; $nowId++) {

            // 実行時点当月（実行時点当日は、就業前とする）
            for ($dayCount = 1; $dayCount < $currentEndOfMonth; $dayCount++) {

                $strDate = $currentYear . '-' . $currentMonth . '-' . $dayCount;
                $paramDay = new Carbon($strDate);
                $starttime = $strDate . ' ' . '8:00:00';
                $endtime   = $strDate . ' ' . '17:00:00';

                // 土日でなければデータを作成
                if (($paramDay->isSaturday() == false) and ($paramDay->isSunday() == false)) {

                    $params[] = [
                        'user_id'   => $nowId,
                        'clock_in'  => $starttime,
                        'clock_out' => $endtime,
                        'status'    => 3,
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

                // 土日でなければデータを作成
                if (($paramDay->isSaturday() == false) and ($paramDay->isSunday() == false)) {

                    $params[] = [
                        'user_id'   => $nowId,
                        'clock_in'  => $starttime,
                        'clock_out' => $endtime,
                        'status'    => 3,
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

                // 土日でなければデータを作成
                if (($paramDay->isSaturday() == false) and ($paramDay->isSunday() == false)) {

                    $params[] = [
                        'user_id'   => $nowId,
                        'clock_in'  => $starttime,
                        'clock_out' => $endtime,
                        'status'    => 3,
                    ];
                }
            }
            // DBへ登録
            DB::table('attendances')->insert($params);
        }
    }
}
