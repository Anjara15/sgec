<?php

namespace App\Enum;

enum Priorite: string
{
    case Normale = 'normale';
    case Haute = 'haute';
    case Urgente = 'urgente';

    public function label(): string
    {
        return match ($this) {
            self::Normale => 'Normale',
            self::Haute => 'Haute',
            self::Urgente => 'Urgente',
        };
    }
}
