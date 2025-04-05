<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StaffDetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attendance_clockin'  => 'required',
            'attendance_clockout' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                  => 'お名前を入力してください',
            'name.max'                       => 'お名前は255文字以内で入力してください',
            'email.required'                 => 'メールアドレスを入力してください',
            'email.email'                    => 'メール形式で入力してください',
            'password.required'              => 'パスワードを入力してください',
            'password.min'                   => 'パスワードは8文字以上で入力してください',
            'password.confirmed'             => '確認パスワードと一致しません',
            'password_confirmation.required' => '確認用パスワードを入力してください',
            'password_confirmation.min'      => '確認用パスワードは8文字以上で入力してください',
            'password_confirmation.same'                  => 'パスワードと一致しません',
        ];
    }

    public function attributes()
    {
        return [
            'name'                  => 'お名前',
            'email'                 => 'メールアドレス',
            'password'              => 'パスワード',
            'password_confirmation' => '確認用パスワード',
        ];
    }

    public function varidateModify($input)
    {
        $param = [
            'clock_in'  => $input['dateline'] . ' ' . $input['attendance_clockin'],
            'clock_out' => $input['dateline'] . ' ' . $input['attendance_clockout'],
            'descript'  => $input['descript'],
        ];
        $paramRule = [];
        $paramMsg = [];
        if ($input['rest_clockin'] <> "" || $input['rest_clockout'] <> "") {
            $param["rest_in"] = $input['dateline'] . ' ' . $input['rest_clockin'];
            $param["rest_out"] = $input['dateline'] . ' ' . $input['rest_clockout'];

            $dateStart = $input['dateline'] . ' ' . $input['attendance_clockin'];
            $dateEnd   = $input['dateline'] . ' ' . $input['attendance_clockout'];
            $paramRule["rest_in"] = ['required', 'date', "after_or_equal:{$dateStart}"];
            $paramRule["rest_out"] = ['required', 'date', "before_or_equal:{$dateEnd}"];

            $paramMsg["rest_in.required"] = "休憩開始時間を入力してください。";
            $paramMsg["rest_in.date"] = "休憩開始時間は日付を入力してください。";
            $paramMsg["rest_in.after_or_equal:{$dateStart}"] = "休憩開始時間が勤務開始より前となっています。";
        }
        if ((int)$input['restSectMax'] > 0) {
            for ($index = 1; $index <= (int)$input['restSectMax']; $index++) {
                if ($input["rest_clockin{$index}"] <> "" || $input["rest_clockout{$index}"] <> "") {
                    $param["rest_in{$index}"] = $input['dateline'] . ' ' . $input["rest_clockin{$index}"];
                    $param["rest_out{$index}"] = $input['dateline'] . ' ' . $input["rest_clockout{$index}"];

                    if ($index == 1) {
                        $dateStart = $param["rest_out"];
                        $dateEnd   = $input['dateline'] . ' ' . $input['attendance_clockout'];
                    } else {
                        $newIndex = $index - 1;
                        $dateStart = $param["rest_out{$newIndex}"];
                        $dateEnd   = $input['dateline'] . ' ' . $input['attendance_clockout'];
                    }
                    $paramRule["rest_in{$index}"] = ['required', 'date', "after_or_equal:{$dateStart}"];
                    $paramRule["rest_out{$index}"] = ['required', 'date', "before_or_equal:{$dateEnd}"];
                }
            }
        }
        $arrayRule = [];
        $arrayRule = $this->rules();
        dd($arrayRule);
    }
}
