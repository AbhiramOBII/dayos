<?php

namespace App\Enums;

enum RoutineType: string
{
    case Behavioural = 'behavioural';
    case Reflective  = 'reflective';

    public function label(): string
    {
        return match ($this) {
            self::Behavioural => 'Behavioural',
            self::Reflective  => 'Reflective',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Behavioural => 'Daily to-do items that build consistent habits',
            self::Reflective  => 'Manifestation, thoughts, and daily musings',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Behavioural => 'bg-blue-100 text-blue-700',
            self::Reflective  => 'bg-purple-100 text-purple-700',
        };
    }
}
