<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pillar_task', function (Blueprint $table) {
            $table->foreignId('pillar_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->primary(['pillar_id', 'task_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pillar_task');
    }
};
