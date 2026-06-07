<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UpskillingGoal extends Model
{
    protected $fillable = ['skill', 'description', 'target_date', 'status', 'ai_roadmap'];

    protected function casts(): array
    {
        return ['target_date' => 'date'];
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
