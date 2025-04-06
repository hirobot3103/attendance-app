<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RestsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // user5人分の休憩データを作成

    }

    function makeRestsBaseDate()
    {
        $attendanceDatas = DB::table('attendances')->all();
    }
}
