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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->dateTime('clock_in')->nullable();  // 就業開始日時
            $table->dateTime('clock_out')->nullable(); // 就業終了日時
            $table->integer('status')->default(0);     // 0:デフォルト(就業前)、1:就業中 2:就業終了 3:休憩中 11～13:申請中 14:申請済
            $table->string('descript', 255)->default("");      // 申請理由 
            $table->timestamp('created_at')->useCurrent()->nullable();
            $table->timestamp('updated_at')->useCurrent()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
