<?php

namespace App\Enums;

enum MeasurementType: string
{
    case Number     = 'number';
    case Days       = 'days';
    case Currency   = 'currency';
    case Percentage = 'percentage';
    case Boolean    = 'boolean';

    public function label(): string
    {
        return match($this) {
            self::Number     => 'Number',
            self::Days       => 'Days',
            self::Currency   => 'Currency (INR)',
            self::Percentage => 'Percentage (%)',
            self::Boolean    => 'Done / Not Done',
        };
    }

    public function unit(): string
    {
        return match($this) {
            self::Number     => '',
            self::Days       => ' days',
            self::Currency   => '₹',
            self::Percentage => '%',
            self::Boolean    => '',
        };
    }

    public function prefix(): bool
    {
        return $this === self::Currency;
    }
}
