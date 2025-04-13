<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('request_rests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained('attendances'); // 対象勤怠データID
            $table->foreignId('req_attendance_id')->constrained('request_attendances'); // 対象勤怠データID
            $table->foreignId('rest_id')->constrained('rests');  // 対象の休憩データID
            $table->dateTime('rest_in')->nullable();  // 休憩開始日時
            $table->dateTime('rest_out')->nullable(); // 休憩終了日時
            $table->timestamp('created_at')->useCurrent()->nullable();
            $table->timestamp('updated_at')->useCurrent()->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_rests');
    }
};
