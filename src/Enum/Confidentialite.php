<?php

namespace App\Enum;

enum Confidentialite: string
{
    case Publique = 'publique';
    case Interne = 'interne';
    case Confidentielle = 'confidentielle';
    case Secret = 'secret';

    public function label(): string
    {
        return match ($this) {
            self::Publique => 'Publique',
            self::Interne => 'Interne',
            self::Confidentielle => 'Confidentielle',
            self::Secret => 'Secret',
        };
    }
}
