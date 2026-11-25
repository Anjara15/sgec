<?php

namespace App\Enum;

enum CourrierType: string
{
    case Entrant = 'entrant';
    case Sortant = 'sortant';
    case Interne = 'interne';

    public function label(): string
    {
        return match ($this) {
            self::Entrant => 'Entrant',
            self::Sortant => 'Sortant',
            self::Interne => 'Interne',
        };
    }
}
