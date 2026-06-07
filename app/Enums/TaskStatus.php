<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Backlog = 'backlog';
    case WIP = 'wip';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Backlog => 'Backlog',
            self::WIP => 'WIP',
            self::Completed => 'Completed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Backlog => 'bg-gray-100 text-gray-600',
            self::WIP => 'bg-blue-100 text-blue-700',
            self::Completed => 'bg-green-100 text-green-700',
        };
    }
}
