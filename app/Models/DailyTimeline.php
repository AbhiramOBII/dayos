<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyTimeline extends Model
{
    protected $fillable = [
        'date',
        'wake_up_time',
        'office_time',
        'lunch_time',
        'come_home_time',
        'dinner_time',
        'sleep_time',
    ];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function formattedTime(string $field): ?string
    {
        $value = $this->$field;
        return $value ? substr($value, 0, 5) : null;
    }
}
