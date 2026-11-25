<?php

namespace App\Enum;

enum CourrierStatut: string
{
    case Brouillon = 'brouillon';
    case Enregistre = 'enregistre';
    case EnValidation = 'en_validation';
    case Valide = 'valide';
    case Rejete = 'rejete';
    case ACorriger = 'a_corriger';
    case EnTransmission = 'en_transmission';
    case Recu = 'recu';
    case Traite = 'traite';
    case Archive = 'archive';

    public function label(): string
    {
        return match ($this) {
            self::Brouillon => 'Brouillon',
            self::Enregistre => 'Enregistré',
            self::EnValidation => 'En validation',
            self::Valide => 'Validé',
            self::Rejete => 'Rejeté',
            self::ACorriger => 'À corriger',
            self::EnTransmission => 'En transmission',
            self::Recu => 'Reçu',
            self::Traite => 'Traité',
            self::Archive => 'Archivé',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Brouillon => 'status-pending',
            self::Enregistre, self::EnValidation, self::EnTransmission, self::ACorriger => 'status-progress',
            self::Valide, self::Recu, self::Traite, self::Archive => 'status-done',
            self::Rejete => 'status-rejected',
        };
    }
}
