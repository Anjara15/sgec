<?php

namespace App\Enum;

enum TransmissionEtat: string
{
    case Envoye = 'envoye';
    case Recu = 'recu';
    case Refuse = 'refuse';
    case Annule = 'annule';

    public function label(): string
    {
        return match ($this) {
            self::Envoye => 'Envoyé',
            self::Recu => 'Reçu',
            self::Refuse => 'Refusé',
            self::Annule => 'Annulé',
        };
    }
}
