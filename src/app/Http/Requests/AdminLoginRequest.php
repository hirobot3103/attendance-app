<?php

namespace App\Http\Requests;

use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;

class  AdminLoginRequest extends FortifyLoginRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'email'    => 'required | email | exists:App\Models\Admin,email',
            'password' => 'required | min:8',
        ];
    }

    public function messages(): array
    {
        $commonErrorMessage = 'ログイン情報が登録されていません';

        return [
            'email.required'    => 'メールアドレスを入力してください',
            'email.email'       => 'メール形式で入力してください',
            'email.exists'      => $commonErrorMessage,
            'password.required' => 'パスワードを入力してください',
            'password.min'      => 'パスワードは8文字以上で入力してください',
        ];
    }

    public function attributes()
    {
        return [
            'email' => 'メールアドレス',
            'password' => 'パスワード',
        ];
    }
}
