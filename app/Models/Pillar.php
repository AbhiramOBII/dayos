<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pillar extends Model
{
    protected $fillable = ['name', 'slug'];

    public function dayThemes(): BelongsToMany
    {
        return $this->belongsToMany(DayTheme::class, 'day_theme_pillar');
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'pillar_task');
    }

}
