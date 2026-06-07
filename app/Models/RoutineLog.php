<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutineLog extends Model
{
    protected $fillable = ['routine_id', 'date', 'is_completed', 'content'];

    protected function casts(): array
    {
        return [
            'date'         => 'date',
            'is_completed' => 'boolean',
        ];
    }

    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }
}
