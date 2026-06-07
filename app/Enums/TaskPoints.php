<?php

namespace App\Enums;

enum TaskPoints: int
{
    case Three = 3;
    case Five = 5;
    case Eight = 8;
    case Thirteen = 13;
    case TwentyOne = 21;
    case ThirtyFour = 34;
    case FiftyFive = 55;

    public function label(): string
    {
        return (string) $this->value;
    }
}
