<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminsTableSeeder extends Seeder
{

    public function run(): void
    {

        // adminsテーブル 管理者ユーザー
        $params = [
            [
                'name'              => '手島 香',
                'email'             => 'administrator@attendance.com',
                'password'          => Hash::make('adminadmin'),
                'email_verified_at' => '2025-02-12 06:38:18',
            ],
        ];

        DB::table('admins')->insert($params);
    }
}
