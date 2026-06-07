<?php

namespace App\Models;

use App\Enums\TaskPoints;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'title',
        'short_description',
        'value_points',
        'status',
        'tbcb_date',
        'is_archived',
        'completed_at',
        'upskilling_goal_id',
    ];

    protected function casts(): array
    {
        return [
            'value_points' => TaskPoints::class,
            'status' => TaskStatus::class,
            'tbcb_date'    => 'date',
            'completed_at' => 'datetime',
            'is_archived'  => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (self $task) {
            if ($task->isDirty('status')) {
                if ($task->status === TaskStatus::Completed && is_null($task->completed_at)) {
                    $task->completed_at = now();
                } elseif ($task->status !== TaskStatus::Completed) {
                    $task->completed_at = null;
                }
            }
        });
    }

    public function pillars(): BelongsToMany
    {
        return $this->belongsToMany(Pillar::class, 'pillar_task');
    }

    public function tbcbLogs(): HasMany
    {
        return $this->hasMany(TaskTbcbLog::class)->latest();
    }

    public function upskillingGoal(): BelongsTo
    {
        return $this->belongsTo(UpskillingGoal::class);
    }
}
