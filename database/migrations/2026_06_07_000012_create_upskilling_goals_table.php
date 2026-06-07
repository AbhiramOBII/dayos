<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upskilling_goals', function (Blueprint $table) {
            $table->id();
            $table->string('skill');
            $table->text('description')->nullable();
            $table->date('target_date');
            $table->string('status')->default('active'); // active, completed, abandoned
            $table->text('ai_roadmap')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upskilling_goals');
    }
};
