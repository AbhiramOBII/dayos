<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PersonMet extends Model
{
    protected $table = 'people_met';

    protected $fillable = [
        'met_at',
        'name',
        'email',
        'phone',
        'company',
        'place',
        'location',
        'context',
        'card_image',
    ];

    protected function casts(): array
    {
        return [
            'met_at' => 'datetime',
        ];
    }

    public function getCardImageUrlAttribute(): ?string
    {
        return $this->card_image
            ? Storage::disk('spaces')->url($this->card_image)
            : null;
    }
}
