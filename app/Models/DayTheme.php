<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DayTheme extends Model
{
    protected $fillable = [
        'title',
        'short_label',
        'description',
        'ideal_day',
        'color',
    ];

    public function pillars(): BelongsToMany
    {
        return $this->belongsToMany(Pillar::class, 'day_theme_pillar');
    }
}
