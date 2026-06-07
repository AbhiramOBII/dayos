<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('upskilling_goal_id')
                  ->nullable()
                  ->after('is_archived')
                  ->constrained('upskilling_goals')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\UpskillingGoal::class);
            $table->dropColumn('upskilling_goal_id');
        });
    }
};
