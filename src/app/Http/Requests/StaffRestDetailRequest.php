<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class StaffRestDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    //     "attendance_clockin" => "09:50"
    //     "attendance_clockout" => "18:07"
    //     "rest_id" => "16"
    //     "rest_clockin" => "10:40"
    //     "rest_clockout" => "11:40"
    //     "rest_id1" => "17"
    //     "rest_clockin1" => "12:10"
    //     "rest_clockout1" => "12:30"
    //     "rest_id2" => "18"
    //     "rest_clockin2" => "13:00"
    //     "rest_clockout2" => "13:45"
    //     "rest_id3" => null
    //     "rest_clockin3" => null
    //     "rest_clockout3" => null
    //     "descript" => "遅刻：通院のため"
    //     "_token" => "afVfUyQo57ziopNJ0O6l5pUgHG9KzkjPEXnZYfzS"
    //     "admin_btn_mod" => null
    //     "id" => "12"
    //     "user_id" => "2"
    //     "name" => "辺見 エリ子"
    //     "dateline" => "2025-04-08"
    //     "status" => "15"
    //     "restSectMax" => "3"
    //     "gardFlg" => "1"
    //     "clock_in" => "2025-04-08 09:50"
    //     "clock_out" => "2025-04-08 18:07"
    //     "rest_in" => "2025-04-08 10:40"
    //     "rest_out" => "2025-04-08 11:40"
    //     "rest_in1" => "2025-04-08 12:10"
    //     "rest_out1" => "2025-04-08 12:30"
    //     "rest_in2" => "2025-04-08 13:00"
    //     "rest_out2" => "2025-04-08 13:45"
    //   ]
    public function varidateRestRelation($varidatedData = [])
    {
        $param = $varidatedData;

        // 休憩時間の重複など矛盾を調べるため、入力されたデータから休憩データのみ取り出して並べ替える
        $restDatas = [];
        foreach ($varidatedData as $key => $value) {
            if (preg_match('/^(rest_in|rest_out)/', $key, $match)) {
                $restDatas[] = [$key => $value];
            }
        }
        $paramRule = [];
        $paramMsg  = [];
        if (!empty($restDatas)) {
            arsort($restDatas);

            // エラーが無ければ、添字：0,偶数＝開始時間、奇数：終了時間となっているか
            for ($index = 0; $index < count($restDatas) - 1; $index++) {
                $startKeyName = array_key_first($restDatas[$index]);
                $endKeyName   = array_key_first($restDatas[$index + 1]);
                $partsKey = substr($startKeyName, -1, 1);

                if ($partsKey == 'n') {
                    $pairKey = 'rest_out';

                    if ($endKeyName <> $pairKey) {
                        $paramRule["{$endKeyName}"] = "after_or_equal:" . $param[$pairKey];
                        $paramMsg["{$endKeyName}.after_or_equal"] = "休憩終了時間が不適切な値です。a";
                    }
                } elseif ($partsKey == 't') {
                    $pairKey = 'rest_in';
                    $paramRule["{$startKeyName}"] = "after_or_equal:" . $param[$pairKey];
                    $paramMsg["{$startKeyName}.after_or_equal"] = "休憩開始時間が不適切な値です。b";
                } else {
                    $pairKey = 'rest_out' . $partsKey;
                    if ($endKeyName <> $pairKey) {
                        $paramRule["{$endKeyName}"] = "after_or_equal:" . $param[$pairKey];
                        $paramMsg["{$endKeyName}.after_or_equal"] = "休憩終了時間が不適切な値です。c";
                    } elseif ($endKeyName == $pairKey) {
                        $paramRule["{$startKeyName}"] = "after_or_equal:" . $param[$pairKey];
                        $paramMsg["{$startKeyName}.after_or_equal"] = "休憩開始時間が不適切な値です。d";
                    }
                }
            }
        }

        return [$param, $paramRule, $paramMsg];
    }

    // // 勤怠、休憩時間同士の関係性をチェックする（逆転や開始時間が被っているかなど）
    // public function varidateRestRelation(Request $request, $varidatedData)
    // {
    //     $param = $varidatedData;

    //     // 

    //     // 勤務時間（休暇を考慮しない）





    //     // rest_Idに値があって、開始・終了ともに空欄の場合

    //     // rest_Idに値がなくて、開始・終了ともに空欄の場合


    //     // 休憩データがある場合は、昇順で並べ替え
    //     // $paramMsg["rest_in.required"]        = "休憩開始時間(hh:mm形式)を入力してください。";
    //     // $paramMsg["rest_in.date"]            = "休憩開始時間(hh:mm形式)で入力してください。";
    //     // $paramMsg["rest_in.after_or_equal"]  = "休憩開始時間が勤務開始時刻{$dateStart}より前となっています。";
    //     // $paramMsg["rest_out.required"]       = "休憩終了時間(hh:mm形式)を入力してください。";
    //     // $paramMsg["rest_out.date"]           = "休憩終了時間(hh:mm形式)で入力してください。";
    //     // $paramMsg["rest_out.before_or_equal"] = "休憩終了時間が退勤時刻{$dateEnd}より後となっています。";

    //     return [$param, $paramRule, $paramMsg];
    // }
}
