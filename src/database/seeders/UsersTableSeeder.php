<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{

    public function run(): void
    {

        // Usersテーブル 一般ユーザー5人分メール認証済み
        $params = [
            [
                'name'              => '山口 達夫',
                'email'             => 'user1@attendance.com',
                'password'          => Hash::make('password1'),
                'email_verified_at' => '2025-02-12 06:38:18',
            ],
            [
                'name'              => '辺見 エリ子',
                'email'             => 'user2@attendance.com',
                'password'          => Hash::make('password2'),
                'email_verified_at' => '2025-02-12 06:38:19',
            ],
            [
                'name'              => '伊豆旗 権蔵',
                'email'             => 'user3@attendance.com',
                'password'          => Hash::make('password2'),
                'email_verified_at' => '2025-02-12 06:39:19',
            ],
            [
                'name'              => '横瀬 泰三',
                'email'             => 'user4@attendance.com',
                'password'          => Hash::make('password2'),
                'email_verified_at' => '2025-02-12 06:40:19',
            ],
            [
                'name'              => 'カルロス 利森',
                'email'             => 'user5@attendance.com',
                'password'          => Hash::make('password2'),
                'email_verified_at' => '2025-02-12 06:41:19',
            ],
        ];

        DB::table('users')->insert($params);
    }
}
