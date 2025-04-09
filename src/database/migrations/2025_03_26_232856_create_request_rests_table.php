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
            $table->integer('attendance_id')->default(0)->nullable();
            $table->integer('req_attendance_id')->default(0)->nullable();
            $table->integer('rest_id')->default(0)->nullable();
            $table->dateTime('rest_in')->nullable();  // 休憩開始日時
            $table->dateTime('rest_out')->nullable(); // 休憩終了日時
            $table->timestamp('created_at')->useCurrent()->nullable();
            $table->timestamp('updated_at')->useCurrent()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_rests');
    }
};
