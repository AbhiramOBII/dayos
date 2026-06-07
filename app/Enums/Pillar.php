<?php

namespace App\Enums;

enum Pillar: string
{
    case Recovery = 'recovery';
    case Envisioning = 'envisioning';
    case Broadcasting = 'broadcasting';
    case Creating = 'creating';
    case Finance = 'finance';
    case Marketing = 'marketing';
    case Operations = 'operations';
    case Learning = 'learning';
    case Networking = 'networking';

    public function label(): string
    {
        return match ($this) {
            self::Recovery => 'Recovery',
            self::Envisioning => 'Envisioning',
            self::Broadcasting => 'Broadcasting',
            self::Creating => 'Creating',
            self::Finance => 'Finance',
            self::Marketing => 'Marketing',
            self::Operations => 'Operations',
            self::Learning => 'Learning',
            self::Networking => 'Networking',
        };
    }
}
