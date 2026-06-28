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
        Schema::create('objective_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objective_id')->constrained('quarterly_objectives')->cascadeOnDelete();
            $table->decimal('value', 12, 2);
            $table->string('note')->nullable();
            $table->date('logged_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('objective_logs');
    }
};
