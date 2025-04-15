<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestId03LoginAdmin extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ログイン管理者認証機能_メールアドレスが未入力の場合バリデーションメッセージが表示される()
    {
        // ログイン画面を開く
        $response = $this->get('/admin/login');
        $response->assertStatus(200);

        // メールアドレス未入力でボタンを押す(送信)
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'passwordtest',
        ]);

        // バリデーションエラー発生とメッセージ内容確認
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /** @test */
    public function 管理者認証機能_パスワードが未入力場合バリデーションメッセージが表示される()
    {

        // 会員登録画面を開く
        $response = $this->get('/admin/login');
        $response->assertStatus(200);

        // パスワード未入力で登録ボタンを押す''
        $response = $this->post('/admin/login', [
            'email' => 'testuser5@test.com',
            'password' => '',
        ]);

        // バリデーションエラー発生とメッセージ内容確認
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /** @test */
    public function 管理者認証機能_登録内容と一致しない場合バリデーションメッセージが表示される()
    {
        // ログイン画面を開く
        $response = $this->get('/admin/login');
        $response->assertStatus(200);

        // 必須項目に間違った情報を入力し登録ボタンを押す
        $response = $this->post('/admin/login', [
            'email' => 'test3@test.com',
            'password' => '12345678',
        ]);

        // バリデーションエラー発生とメッセージ内容確認
        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }
}
