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
        Schema::create('quarterly_objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->date('start_date');
            $table->enum('measurement_type', ['number', 'days', 'currency', 'percentage', 'boolean'])->default('number');
            $table->decimal('target', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('objective_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objective_id')->constrained('quarterly_objectives')->cascadeOnDelete();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->decimal('contribution', 10, 2)->nullable()->comment('Override value; null = use task value_points');
            $table->unique(['objective_id', 'task_id']);
        });

        Schema::create('objective_routines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objective_id')->constrained('quarterly_objectives')->cascadeOnDelete();
            $table->foreignId('routine_id')->constrained('routines')->cascadeOnDelete();
            $table->decimal('contribution_per_completion', 10, 2)->default(1)->comment('Added each time routine is logged complete');
            $table->unique(['objective_id', 'routine_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('objective_routines');
        Schema::dropIfExists('objective_tasks');
        Schema::dropIfExists('quarterly_objectives');
    }
};
