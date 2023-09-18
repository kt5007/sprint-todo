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
        Schema::create('user_availabilities', function (Blueprint $table) {
            $table->id(); // 自動増分ID
            $table->date('date'); // 日にち (日付)
            $table->float('available_time', 6, 1); // 空き時間 (浮動小数点数)
            $table->unsignedBigInteger('user_id'); // ユーザー ID (整数)
            $table->timestamps(); // 作成日時と更新日時
    
            // 外部キー制約: user_id カラムは users テーブルの id カラムを参照
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_availabilities');
    }
};
