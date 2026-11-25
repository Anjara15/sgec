<?php

namespace App\Service;

use App\Enum\CourrierStatut;
use App\Enum\CourrierType;
use App\Repository\CourrierRepository;
use App\Repository\JournalActiviteRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;

class DashboardStatsService
{
    public function __construct(
        private readonly CourrierRepository $courrierRepository,
        private readonly UserRepository $userRepository,
        private readonly ServiceRepository $serviceRepository,
        private readonly JournalActiviteRepository $journalActiviteRepository,
    ) {
    }

    /** @return array<string, mixed> */
    public function getStats(): array
    {
        $parStatut = $this->courrierRepository->statsParStatut();
        $parType = $this->courrierRepository->statsParType();

        return [
            'utilisateurs_actifs' => $this->userRepository->countActifs(),
            'services' => $this->serviceRepository->count([]),
            'courriers' => $this->courrierRepository->countNonArchives(),
            'entrants' => $parType[CourrierType::Entrant->value] ?? 0,
            'sortants' => $parType[CourrierType::Sortant->value] ?? 0,
            'en_attente' => ($parStatut[CourrierStatut::EnValidation->value] ?? 0)
                + ($parStatut[CourrierStatut::Enregistre->value] ?? 0)
                + ($parStatut[CourrierStatut::ACorriger->value] ?? 0),
            'valides' => $parStatut[CourrierStatut::Valide->value] ?? 0,
            'archives' => $parStatut[CourrierStatut::Archive->value] ?? 0,
            'traites' => $parStatut[CourrierStatut::Traite->value] ?? 0,
            'par_type' => $parType,
            'par_statut' => $parStatut,
            'recents' => $this->courrierRepository->findRecents(8),
            'activites' => $this->journalActiviteRepository->findRecents(8),
        ];
    }
}
