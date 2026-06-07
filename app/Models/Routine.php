<?php

namespace App\Models;

use App\Enums\RoutineType;
use Illuminate\Database\Eloquent\Model;

class Routine extends Model
{
    protected $fillable = [
        'title',
        'description',
        'type',
        'prompt',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type'      => RoutineType::class,
            'is_active' => 'boolean',
        ];
    }
}
