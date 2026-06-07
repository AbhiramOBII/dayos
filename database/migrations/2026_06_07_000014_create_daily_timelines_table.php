<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_timelines', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->time('wake_up_time')->nullable();
            $table->time('office_time')->nullable();
            $table->time('lunch_time')->nullable();
            $table->time('come_home_time')->nullable();
            $table->time('dinner_time')->nullable();
            $table->time('sleep_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_timelines');
    }
};
