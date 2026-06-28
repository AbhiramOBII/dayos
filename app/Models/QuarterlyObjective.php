<?php

namespace App\Models;

use App\Enums\MeasurementType;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class QuarterlyObjective extends Model
{
    protected $fillable = [
        'user_id', 'title', 'start_date',
        'measurement_type', 'target', 'notes', 'is_active',
    ];

    protected $casts = [
        'measurement_type' => MeasurementType::class,
        'target'           => 'float',
        'is_active'        => 'boolean',
        'start_date'       => 'date',
    ];

    public function getEndDateAttribute(): \Carbon\Carbon
    {
        return $this->start_date->copy()->addDays(30);
    }

    public function getDaysRemainingAttribute(): int
    {
        $remaining = (int) now()->startOfDay()->diffInDays($this->end_date, false);
        return max(0, $remaining);
    }

    public function getDaysElapsedAttribute(): int
    {
        $elapsed = (int) $this->start_date->startOfDay()->diffInDays(now()->startOfDay(), false);
        return max(0, min(30, $elapsed));
    }

    public function isActive(): bool
    {
        return now()->between($this->start_date, $this->end_date);
    }

    public function isPast(): bool
    {
        return now()->isAfter($this->end_date);
    }

    public function isUpcoming(): bool
    {
        return now()->isBefore($this->start_date);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'objective_tasks', 'objective_id', 'task_id')
                    ->withPivot('contribution');
    }

    public function routines(): BelongsToMany
    {
        return $this->belongsToMany(Routine::class, 'objective_routines', 'objective_id', 'routine_id')
                    ->withPivot('contribution_per_completion');
    }

    public function logs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ObjectiveLog::class, 'objective_id')->orderByDesc('logged_date');
    }

    /**
     * Calculate current progress based on linked completed tasks + routine logs.
     * Returns a float representing the accumulated value.
     */
    public function computeProgress(): float
    {
        $progress = 0.0;

        // Tasks: use override contribution or task's value_points
        foreach ($this->tasks as $task) {
            if ($task->status === TaskStatus::Completed) {
                $contribution = $task->pivot->contribution ?? $task->value_points->value;
                $progress += (float) $contribution;
            }
        }

        // Routines: count all RoutineLogs within the 30-day window
        $start = $this->start_date->toDateString();
        $end   = $this->end_date->toDateString();
        foreach ($this->routines as $routine) {
            $completions = RoutineLog::where('routine_id', $routine->id)
                ->where('is_completed', true)
                ->whereBetween('date', [$start, $end])
                ->count();
            $progress += $completions * (float) $routine->pivot->contribution_per_completion;
        }

        // Manual logs entered from the daily tracker
        $progress += ObjectiveLog::where('objective_id', $this->id)
            ->whereBetween('logged_date', [$start, $end])
            ->sum('value');

        return $progress;
    }

    public function progressPercent(): int
    {
        if ($this->measurement_type === MeasurementType::Boolean) {
            return $this->computeProgress() >= 1 ? 100 : 0;
        }
        if ($this->target <= 0) return 0;
        return (int) min(100, round(($this->computeProgress() / $this->target) * 100));
    }

    public function formattedProgress(): string
    {
        $val = $this->computeProgress();
        return $this->formatValue($val);
    }

    public function formattedTarget(): string
    {
        return $this->formatValue($this->target);
    }

    private function formatValue(float $val): string
    {
        return match($this->measurement_type) {
            MeasurementType::Currency   => '₹' . number_format($val, 0),
            MeasurementType::Percentage => number_format($val, 1) . '%',
            MeasurementType::Days       => number_format($val, 0) . ' days',
            MeasurementType::Boolean    => $val >= 1 ? 'Done' : 'Not done',
            default                     => number_format($val, 0),
        };
    }

}
