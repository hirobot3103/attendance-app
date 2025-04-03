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

    public function rules(int $maxSction): array
    {

        $paramRules = [];
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
}
