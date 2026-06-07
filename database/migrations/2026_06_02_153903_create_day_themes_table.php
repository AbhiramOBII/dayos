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
        Schema::create('day_themes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('short_label', 50);
            $table->text('description')->nullable();
            $table->json('pillars');
            $table->text('ideal_day')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('day_themes');
    }
};
