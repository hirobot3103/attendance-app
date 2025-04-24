<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

use App\Models\User;

use Tests\TestCase;

class TestId05GetStatus extends TestCase
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
    public function ステータス確認機能_勤務外の場合、勤怠ステータスが正しく表示される()
    {
        $respose = $this->get('/login');
        $this->actingAs($this->user);
        $respose = $this->get('/attendance');
        $respose->assertSee('勤務外', false);
    }

    /** @test */
    public function ステータス確認機能_出勤中の場合、勤怠ステータスが正しく表示される()
    {
        $respose = $this->get('/login');
        $this->actingAs($this->user);
        $respose = $this->get('/attendance');
        $respose = $this->post(
            '/attendance',
            [
                'clock_in' => null,
            ]
        );
        $respose = $this->get('/attendance');
        $respose->assertSee('出勤中', false);
    }

    /** @test */
    public function ステータス確認機能_休憩中の場合、勤怠ステータスが正しく表示される()
    {
        $respose = $this->get('/login');
        $this->actingAs($this->user);
        $respose = $this->get('/attendance');
        $respose = $this->post(
            '/attendance',
            [
                'clock_in' => null,
            ]
        );
        $respose = $this->get('/attendance');
        $respose = $this->post(
            '/attendance',
            [
                'rest_in' => null,
            ]
        );
        $respose = $this->get('/attendance');
        $respose->assertSee('休憩中', false);
    }

    /** @test */
    public function ステータス確認機能_退勤済みの場合、勤怠ステータスが正しく表示される()
    {
        $respose = $this->get('/login');
        $this->actingAs($this->user);
        $respose = $this->get('/attendance');
        $respose = $this->post(
            '/attendance',
            [
                'clock_in' => null,
            ]
        );
        $respose = $this->get('/attendance');
        $respose = $this->post(
            '/attendance',
            [
                'rest_in' => null,
            ]
        );
        $respose = $this->get('/attendance');
        $respose = $this->post(
            '/attendance',
            [
                'rest_out' => null,
            ]
        );
        $respose = $this->get('/attendance');
        $respose = $this->post(
            '/attendance',
            [
                'clock_out' => null,
            ]
        );
        $respose = $this->get('/attendance');
        $respose->assertSee('退勤済', false);
    }
}
