<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pillars', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        DB::table('pillars')->insert([
            ['name' => 'Recovery',    'slug' => 'recovery',    'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Envisioning', 'slug' => 'envisioning', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Broadcasting','slug' => 'broadcasting','created_at' => now(), 'updated_at' => now()],
            ['name' => 'Creating',    'slug' => 'creating',    'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Finance',     'slug' => 'finance',     'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Marketing',   'slug' => 'marketing',   'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Operations',  'slug' => 'operations',  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Learning',    'slug' => 'learning',    'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Networking',  'slug' => 'networking',  'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('pillars');
    }
};
