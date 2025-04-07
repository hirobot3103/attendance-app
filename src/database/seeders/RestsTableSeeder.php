<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RestsTableSeeder extends Seeder
{
    public function run(): void
    {
        // attendancesテーブルに対応した休憩データを作成
        $this->makeRestsBaseDate();

        // request_attendancesテーブルに対応した休憩の申請データを作成
        $this->makeRestsRequestDate();
    }

    private function makeRestsBaseDate()
    {
        $attendanceDatas = DB::table('attendances')->get();

        $params = [];
        $restDataCountBase = [1, 1, 1, 2, 1, 2, 3, 1, 3, 2, 1, 1, 1, 1, 2, 1, 2, 1];
        foreach ($attendanceDatas as $rowData) {

            // 取得した休憩回数をランダムに設定
            $restDataCount =  $restDataCountBase[rand(1, count($restDataCountBase) - 1)];

            // 最初の休憩開始の時間帯を
            for ($index = 1; $index <= $restDataCount; $index++) {

                $nextRestMin = [30, 40, 50, 60];
                $addMin = $nextRestMin[rand(0, 3)];  // 当該時刻から休憩開始までの間隔

                if ($index == 1) {
                    $restStart = new Carbon($rowData->clock_in);
                    $restEnd = new Carbon($rowData->clock_in);
                    $restMin = 60; // 休憩時間
                } else {
                    $nextRestTime = [5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60];
                    $restMin = $nextRestTime[rand(0, count($nextRestTime) - 1)];; // 休憩時間                
                }

                $rtnStart = $restStart->addMinutes($addMin);
                $rtnEnd   = $restEnd->addMinutes($addMin + $restMin);

                $params[] = [
                    'attendance_id' => $rowData->id,
                    'rest_in'       => $rtnStart,
                    'rest_out'      => $rtnEnd,
                ];

                $restStart = new Carbon($rtnEnd);
                $restEnd = new Carbon($rtnEnd);
            }
        }
        DB::table('rests')->insert($params);
    }

    private function makeRestsRequestBaseDate($rowData)
    {
        $params = [];

        $restDatas = DB::table('rests')
            ->where('attendance_id', $rowData->attendance_id)
            ->get();

        $index = 0;
        foreach ($restDatas as $restdata) {
            $nextRestMin = [30, 40, 50, 60];
            $addMin = $nextRestMin[rand(0, 3)];  // 当該時刻から休憩開始までの間隔

            if ($index == 0) {
                $restStart = new Carbon($rowData->clock_in);
                $restEnd = new Carbon($rowData->clock_in);
                $restMin = 60; // 休憩時間
            } else {
                $nextRestTime = [5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60];
                $restMin = $nextRestTime[rand(0, count($nextRestTime) - 1)];; // 休憩時間                
            }

            $rtnStart = $restStart->addMinutes($addMin);
            $rtnEnd   = $restEnd->addMinutes($addMin + $restMin);

            $params[] = [
                'attendance_id' => $rowData->attendance_id,
                'rest_id'       => $restdata->id,
                'rest_in'       => $rtnStart,
                'rest_out'      => $rtnEnd,
            ];

            $restStart = new Carbon($rtnEnd);
            $restEnd = new Carbon($rtnEnd);
            $index++;
        }
        return $params;
    }

    private function makeRestsRequestDate()
    {
        $attendanceDatas = DB::table('request_attendances')->get();

        $params = [];
        foreach ($attendanceDatas as $rowData) {

            if ($rowData->status == 12) {

                // 休憩時間を再構築
                $rtnParams = $this->makeRestsRequestBaseDate($rowData);
                foreach ($rtnParams as $oneParam) {
                    $params[] = [
                        'attendance_id' => $oneParam["attendance_id"],
                        'rest_id'       => $oneParam["rest_id"],
                        'rest_in'       => $oneParam["rest_in"],
                        'rest_out'      => $oneParam["rest_out"],
                    ];
                }
            } elseif ($rowData->status == 15) {

                $restDatas = DB::table('rests')
                    ->where('attendance_id', $rowData->attendance_id)
                    ->get();

                foreach ($restDatas as $restRowData) {
                    $params[] = [
                        'attendance_id' => $rowData->attendance_id,
                        'rest_id'       => $restRowData->id,
                        'rest_in'       => $restRowData->rest_in,
                        'rest_out'      => $restRowData->rest_out,
                    ];
                }
            }
        }
        DB::table('request_rests')->insert($params);
    }
    // 休憩入力欄の不具合、一般ユーザーでのバリデーションとviewの修正、休憩同士の関係チェック、viewレイアウトの調整（エラー部分と備考、修正ボタン等）
}
