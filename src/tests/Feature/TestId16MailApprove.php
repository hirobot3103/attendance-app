<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

use App\Models\User;
use Tests\TestCase;

class TestId16MailApprove extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setup(): void
    {
        parent::setup();

        Artisan::call('migrate:refresh', ['--env' => 'testing']);
        $this->seed();

        $this->user = User::create(
            [
                'name' => 'testman',
                'email' => 'testman@attendance.com',
                'password' => 'passwordtest',
                'email_verified_at' => now(),
            ]
        );
    }

    /** @test */
    public function メール認証機能_会員登録後、認証メールが送信される()
    {

        $response = $this->get('/register');
        $response->assertStatus(200);

        $paramUser = [
            'name' => 'testmailman',
            'email' => 'testmailman@attendance.com',
            'password' => 'passwordmail',
            'password_confirmation' => 'passwordmail',
        ];

        $response = $this->post('/register', $paramUser);
        $userData = User::where('name', 'testmailman')->first();
        $this->actingAs($userData);
        $response = $this->get('/attendance')->assertRedirect(route('verification.notice'));
        $response = $this->post('/logout');
    }

    /** @test */
    public function メール認証機能_メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する()
    {

        $response = $this->get('/register');
        $response->assertStatus(200);

        $paramUser = [
            'name' => 'testmailman',
            'email' => 'testmailman@attendance.com',
            'password' => 'passwordmail',
            'password_confirmation' => 'passwordmail',
        ];

        $response = $this->post('/register', $paramUser);
        $userData = User::where('name', 'testmailman')->first();
        $this->actingAs($userData);
        $response = $this->get('/attendance')->assertRedirect(route('verification.notice'));

        // 外部のmailhogへのリクエストを作り、接続出来たことにする
        Http::fake([
            'http://localhost:8025' => Http::response('Mocked response content', 200),
        ]);
        $response = Http::get('http://localhost:8025');

        // 受け取ったレスポンスを確認
        $this->assertEquals('Mocked response content', $response->body());
        $response = $this->post('/logout');
    }

    /** @test */
    public function メール認証機能_メール認証サイトのメール認証を完了すると、一覧ページに遷移する()
    {

        $response = $this->get('/register');
        $response->assertStatus(200);

        $paramUser = [
            'name' => 'testmailman',
            'email' => 'testmailman@attendance.com',
            'password' => 'passwordmail',
            'password_confirmation' => 'passwordmail',
        ];

        $response = $this->post('/register', $paramUser);
        $userData = User::where('name', 'testmailman')->first();

        // 登録した一般ユーザー用の認証URLを作成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $userData->id, 'hash' => sha1($userData->getEmailForVerification())]
        );

        // メールから押下
        $response = $this->get($verificationUrl)->assertRedirect('/attendance');
    }
}
