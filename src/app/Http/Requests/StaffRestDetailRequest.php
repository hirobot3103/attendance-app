<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class StaffRestDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            //
        ];
    }

    // 休憩時間同士の関係性をチェックする（開始時間が被っているかなど）
    // 休憩時間入力のチェック済みが前提
    public function varidateRestRelation(Request $request)
    {
        $input = [];
        foreach ($request->all() as $key => $val) {
            $input[$key] = $val;
        }
        $param = $input;

        // 勤務時間（休暇を考慮しない）




        // rest_Idに値があって、開始・終了ともに空欄の場合

        // rest_Idに値がなくて、開始・終了ともに空欄の場合


        // 休憩データがある場合は、昇順で並べ替え
        // $paramMsg["rest_in.required"]        = "休憩開始時間(hh:mm形式)を入力してください。";
        // $paramMsg["rest_in.date"]            = "休憩開始時間(hh:mm形式)で入力してください。";
        // $paramMsg["rest_in.after_or_equal"]  = "休憩開始時間が勤務開始時刻{$dateStart}より前となっています。";
        // $paramMsg["rest_out.required"]       = "休憩終了時間(hh:mm形式)を入力してください。";
        // $paramMsg["rest_out.date"]           = "休憩終了時間(hh:mm形式)で入力してください。";
        // $paramMsg["rest_out.before_or_equal"] = "休憩終了時間が退勤時刻{$dateEnd}より後となっています。";

        return [$param, $paramRule, $paramMsg];
    }
}
