<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $themes = DB::table('day_themes')->get();

        foreach ($themes as $theme) {
            $slugs = json_decode($theme->pillars, true) ?? [];

            foreach ($slugs as $slug) {
                $pillar = DB::table('pillars')->where('slug', $slug)->first();

                if ($pillar) {
                    DB::table('day_theme_pillar')->insertOrIgnore([
                        'day_theme_id' => $theme->id,
                        'pillar_id'    => $pillar->id,
                    ]);
                }
            }
        }

        Schema::table('day_themes', function (Blueprint $table) {
            $table->dropColumn('pillars');
        });
    }

    public function down(): void
    {
        Schema::table('day_themes', function (Blueprint $table) {
            $table->json('pillars')->nullable();
        });
    }
};
