<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class TestId04GetDate extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 日時取得機能_現在の日時情報がUIと同じ形式で出力されている()
    {
        $this->seed();

        // ログイン画面を開く
        $response = $this->get('/login');
        $response->assertStatus(200);

        $response = $this->post('/login', [
            'email' => 'user1@attendance.com',
            'password' => 'password1',
        ]);

        //  現時点と比較する
        $response = $this->get('/attendance');
        $response->assertViewIs('top');
        $serverTime = new Carbon();
        $searchString = '<input type="hidden" value="' . $serverTime->format('Y-m-d h:i') . '">';
        $response->assertSee($searchString, false);
    }
}
