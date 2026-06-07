<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routine_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routine_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->boolean('is_completed')->default(false);
            $table->text('content')->nullable();
            $table->timestamps();

            $table->unique(['routine_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routine_logs');
    }
};
