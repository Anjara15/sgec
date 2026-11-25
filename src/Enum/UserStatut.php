<?php

namespace App\Enum;

enum UserStatut: string
{
    case Actif = 'actif';
    case Inactif = 'inactif';
    case Suspendu = 'suspendu';

    public function label(): string
    {
        return match ($this) {
            self::Actif => 'Actif',
            self::Inactif => 'Inactif',
            self::Suspendu => 'Suspendu',
        };
    }
}
