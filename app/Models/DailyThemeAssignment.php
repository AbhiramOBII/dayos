<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyThemeAssignment extends Model
{
    protected $fillable = ['day_theme_id', 'date'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function dayTheme(): BelongsTo
    {
        return $this->belongsTo(DayTheme::class);
    }
}
