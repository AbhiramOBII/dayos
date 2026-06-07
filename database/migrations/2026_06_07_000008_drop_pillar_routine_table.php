<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('pillar_routine');
    }

    public function down(): void
    {
        Schema::create('pillar_routine', function (Blueprint $table) {
            $table->foreignId('pillar_id')->constrained()->cascadeOnDelete();
            $table->foreignId('routine_id')->constrained()->cascadeOnDelete();
            $table->primary(['pillar_id', 'routine_id']);
        });
    }
};
