<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class StaffDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clock_in'  => ['required', 'date'],
            'clock_out' => ['required', 'date'],
            'descript' => ['required', 'max:255',],
        ];
    }

    public function messages(): array
    {
        return [
            'clock_in.required'  => "勤務開始時間を入力してください",
            'clock_in.date'      => "勤務開始時間は日付で入力してください",
            'clock_out.required' => "退勤時間を入力してください",
            'clock_out.date'     => "勤務開始時間は日付で入力してください",
            'descript.required'  => '備考を記入してください',
            'descript.max'       => '備考は255文字以内で入力してください',
        ];
    }

    // 勤怠入力関係のバリデーションルール、エラーメッセージをセット
    // <戻値>
    // $param:検証する値を格納した配列(一部値を加工)
    // $paramRule:検証方法
    // $paramMsg:エラーメッセージ
    public function varidateModify(Request $request)
    {
        $input = [];
        foreach ($request->all() as $key => $val) {
            $input[$key] = $val;
        }
        $param = $input;
        $paramRule = $this->rules();
        $paramMsg = $this->messages();
        $param['clock_in'] = $input['attendance_clockin'] ? $input['dateline'] . ' ' . $input['attendance_clockin'] : "";
        $param['clock_out'] = $input['attendance_clockout'] ? $input['dateline'] . ' ' . $input['attendance_clockout'] : "";

        // 出勤と退勤両方入力されている場合に適用するルールとメッセージを追加
        if (($param['clock_in'] <> "") && ($param['clock_out'] <> "")) {
            $dateStart = $input['dateline'] . ' ' . $input['attendance_clockin'];
            $dateEnd   = $input['dateline'] . ' ' . $input['attendance_clockout'];
            $paramRule["clock_in"][2]  = "before_or_equal:{$dateEnd}";
            $paramRule["clock_out"][2] = "after_or_equal:{$dateStart}";

            $msg = "出勤時間もしくは退勤時間が不適切な値です。";
            $paramMsg["clock_in.before_or_equal"]  = $msg;
            $paramMsg["clock_out.after_or_equal"]  = $msg;
        }

        $dateStart = $input['dateline'] . ' ' . $input['attendance_clockin'];
        $dateEnd   = $input['dateline'] . ' ' . $input['attendance_clockout'];

        $param['descript']  = $input['descript'];

        // 休憩時間に係るルールとメッセージをセット
        if (($input['rest_clockin'] <> "") || ($input['rest_clockout'] <> "")) {
            $param["rest_in"] = $input['rest_clockin'] ? $input['dateline'] . ' ' . $input['rest_clockin'] : "";
            $param["rest_out"] = $input['rest_clockout'] ? $input['dateline'] . ' ' . $input['rest_clockout'] : "";

            $paramRule["rest_in"] = ['required', 'date',];
            $paramRule["rest_out"] = ['required', 'date',];
            $paramMsg["rest_in.required"]        = "休憩開始時間(hh:mm形式)を入力してください。";
            $paramMsg["rest_in.date"]            = "休憩開始時間(hh:mm形式)で入力してください。";
            $paramMsg["rest_out.required"]       = "休憩終了時間(hh:mm形式)を入力してください。";
            $paramMsg["rest_out.date"]           = "休憩終了時間(hh:mm形式)で入力してください。";
            $msg = "休憩時間が勤務時間外です。";

            if ($param['clock_in'] <> "") {
                $paramRule["rest_in"][2] = "after_or_equal:{$dateStart}";
                $paramRule["rest_out"][2] = "after_or_equal:{$dateStart}";
                $paramMsg["rest_in.after_or_equal"]  = $msg;
                $paramMsg["rest_out.after_or_equal"]  = $msg;
            }
            if ($param['clock_out'] <> "") {
                $paramRule["rest_in"][3] = "before_or_equal:{$dateEnd}";
                $paramRule["rest_out"][3] = "before_or_equal:{$dateEnd}";
                $paramMsg["rest_in.before_or_equal"]  = $msg;
                $paramMsg["rest_out.before_or_equal"]  = $msg;
            }
        }

        if ((int)$input['restSectMax'] > 0) {
            for ($index = 1; $index <= (int)$input['restSectMax']; $index++) {
                if (($input["rest_clockin{$index}"] <> "") || ($input["rest_clockout{$index}"] <> "")) {
                    $param["rest_in{$index}"] = $input["rest_clockin{$index}"] ? $input['dateline'] . ' ' . $input["rest_clockin{$index}"] : "";
                    $param["rest_out{$index}"] = $input["rest_clockout{$index}"] ? $input['dateline'] . ' ' . $input["rest_clockout{$index}"] : "";

                    $paramRule["rest_in{$index}"] = ['required', 'date',];
                    $paramRule["rest_out{$index}"] = ['required', 'date',];
                    $paramMsg["rest_in{$index}.required"]        = "休憩開始時間(hh:mm形式)を入力してください。";
                    $paramMsg["rest_in{$index}.date"]            = "休憩開始時間(hh:mm形式)で入力してください。";
                    $paramMsg["rest_out{$index}.required"]       = "休憩終了時間(hh:mm形式)を入力してください。";
                    $paramMsg["rest_out{$index}.date"]           = "休憩終了時間(hh:mm形式)で入力してください。";
                    $msg = "休憩時間が勤務時間外です。";

                    if ($param['clock_in'] <> "") {
                        $paramRule["rest_in{$index}"][2] = "after_or_equal:{$dateStart}";
                        $paramRule["rest_out{$index}"][2] = "after_or_equal:{$dateStart}";
                        $paramMsg["rest_in{$index}.after_or_equal"]  = $msg;
                        $paramMsg["rest_out{$index}.after_or_equal"]  = $msg;
                    }
                    if ($param['clock_out'] <> "") {
                        $paramRule["rest_in{$index}"][3] = "before_or_equal:{$dateEnd}";
                        $paramRule["rest_out{$index}"][3] = "before_or_equal:{$dateEnd}";
                        $paramMsg["rest_in{$index}.before_or_equal"]  = $msg;
                        $paramMsg["rest_out{$index}.before_or_equal"]  = $msg;
                    }
                }
            }
        }
        return [$paramRet, $paramRuleRet, $paramMsgRet] = $this->varidateRestRelation($param, $paramRule, $paramMsg);
    }

    public function varidateRestRelation($param, $paramRule, $paramMsg)
    {
        // 休憩時間の重複など矛盾を調べるため、入力されたデータから休憩データのみ取り出して並べ替える
        $restDatas = [];
        foreach ($param as $key => $value) {
            if (preg_match('/^(rest_in|rest_out)/', $key, $match)) {
                $restBaseDatas[$key] = $value;
            }
        }
        if (!empty($restBaseDatas)) {
            asort($restBaseDatas);
            $newIndex = 0;
            $restDatas = [];
            foreach ($restBaseDatas as $key => $value) {
                $restDatas[$newIndex] = [$key => $value];
                $newIndex++;
            }

            // エラーが無ければ、添字：0,偶数＝開始時間、奇数：終了時間となっているか
            for ($index = 0; $index < count($restDatas) - 1; $index += 1) {
                $startKeyName = array_key_first($restDatas[$index]);
                $endKeyName   = array_key_first($restDatas[$index + 1]);
                $partsKey = substr($startKeyName, -1, 1);

                if ($partsKey == 'n') {
                    $pairKey = 'rest_out';

                    if ($endKeyName <> $pairKey) {
                        $paramRule["{$endKeyName}"][] = "after_or_equal:" . $param[$pairKey];
                        $paramMsg["{$endKeyName}.after_or_equal"] = "休憩時間帯が他の休憩と被っているか、不適切な値です。";
                    }
                } elseif ($partsKey == 't') {
                    $pairKey = 'rest_in';
                    $paramRule["{$startKeyName}"][] = "after_or_equal:" . $param[$pairKey];
                    $paramMsg["{$startKeyName}.after_or_equal"] = "休憩時間帯が他の休憩と被っているか、不適切な値です。";
                } else {
                    $pairKey = 'rest_out' . $partsKey;
                    if ($endKeyName <> $pairKey) {
                        $paramRule["{$endKeyName}"][] = "after_or_equal:" . $param[$pairKey];
                        $paramMsg["{$endKeyName}.after_or_equal"] = "休憩時間帯が他の休憩と被っているか、不適切な値です。";
                    }
                }
            }
        }
        return [$param, $paramRule, $paramMsg];
    }
}
