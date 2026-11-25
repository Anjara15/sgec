<?php

namespace App\Service;

use App\Entity\Courrier;
use App\Entity\HistoriqueStatut;
use App\Entity\Transmission;
use App\Entity\User;
use App\Enum\CourrierStatut;
use App\Enum\TransmissionEtat;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CourrierWorkflowService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly NotificationService $notificationService,
        private readonly JournalService $journalService,
    ) {
    }

    private function changerStatut(Courrier $courrier, CourrierStatut $nouveau, User $auteur, ?string $motif = null): void
    {
        $ancien = $courrier->getStatut();
        if ($ancien === $nouveau) {
            return;
        }

        $historique = (new HistoriqueStatut())
            ->setCourrier($courrier)
            ->setAncienStatut($ancien)
            ->setNouveauStatut($nouveau)
            ->setAuteur($auteur)
            ->setMotif($motif);

        $courrier->setStatut($nouveau);
        $courrier->setDateModification(new \DateTimeImmutable());
        $courrier->setMotifValidation($motif);
        $courrier->addHistoriqueStatut($historique);

        if ($nouveau === CourrierStatut::Archive) {
            $courrier->setDateArchivage(new \DateTimeImmutable());
        }

        $this->em->persist($historique);
        $this->em->flush();

        $this->journalService->log(
            $auteur,
            sprintf('Changement de statut %s → %s', $ancien->label(), $nouveau->label()),
            'Courrier',
            $courrier->getId(),
            ['motif' => $motif]
        );

        $cibles = array_filter([
            $courrier->getCreateur(),
            $courrier->getAgentResponsable(),
        ], static fn (?User $u) => $u && $u->getId() !== $auteur->getId());

        foreach (array_unique($cibles, SORT_REGULAR) as $cible) {
            $this->notificationService->notify(
                $cible,
                'Changement de statut',
                sprintf('Le courrier %s est passé à « %s ».', $courrier->getReference(), $nouveau->label()),
                $courrier
            );
        }
    }

    public function soumettreValidation(Courrier $courrier, User $auteur): void
    {
        $this->assurerTransition($courrier, [CourrierStatut::Enregistre, CourrierStatut::ACorriger, CourrierStatut::Brouillon]);
        $this->changerStatut($courrier, CourrierStatut::EnValidation, $auteur, 'Soumis pour validation');
    }

    public function approuver(Courrier $courrier, User $auteur, ?string $motif = null): void
    {
        $this->assurerTransition($courrier, [CourrierStatut::EnValidation]);
        $this->changerStatut($courrier, CourrierStatut::Valide, $auteur, $motif ?? 'Approuvé');
    }

    public function rejeter(Courrier $courrier, User $auteur, string $motif): void
    {
        if (trim($motif) === '') {
            throw new BadRequestHttpException('Un motif est obligatoire pour rejeter.');
        }
        $this->assurerTransition($courrier, [CourrierStatut::EnValidation]);
        $this->changerStatut($courrier, CourrierStatut::Rejete, $auteur, $motif);
    }

    public function demanderCorrection(Courrier $courrier, User $auteur, string $motif): void
    {
        if (trim($motif) === '') {
            throw new BadRequestHttpException('Un motif est obligatoire pour demander une correction.');
        }
        $this->assurerTransition($courrier, [CourrierStatut::EnValidation]);
        $this->changerStatut($courrier, CourrierStatut::ACorriger, $auteur, $motif);
    }

    public function archiver(Courrier $courrier, User $auteur, ?string $motif = null): void
    {
        $this->assurerTransition($courrier, [
            CourrierStatut::Traite,
            CourrierStatut::Valide,
            CourrierStatut::Recu,
            CourrierStatut::Rejete,
        ]);
        $this->changerStatut($courrier, CourrierStatut::Archive, $auteur, $motif ?? 'Archivage');
    }

    public function traiter(Courrier $courrier, User $auteur, ?string $motif = null): void
    {
        $this->assurerTransition($courrier, [CourrierStatut::Recu]);
        $this->changerStatut($courrier, CourrierStatut::Traite, $auteur, $motif ?? 'Traitement terminé');
    }

    public function transmettre(Courrier $courrier, User $expediteur, User $destinataire, ?string $commentaire = null): Transmission
    {
        $this->assurerTransition($courrier, [CourrierStatut::Valide, CourrierStatut::Recu]);

        $transmission = (new Transmission())
            ->setCourrier($courrier)
            ->setExpediteur($expediteur)
            ->setDestinataire($destinataire)
            ->setServiceOrigine($expediteur->getService())
            ->setServiceDestinataire($destinataire->getService())
            ->setCommentaire($commentaire)
            ->setEtat(TransmissionEtat::Envoye);

        $courrier->addTransmission($transmission);
        $courrier->setAgentResponsable($destinataire);

        $this->em->persist($transmission);
        $this->changerStatut($courrier, CourrierStatut::EnTransmission, $expediteur, $commentaire);

        $this->notificationService->notify(
            $destinataire,
            'Transmission reçue',
            sprintf('Le courrier %s vous a été transmis.', $courrier->getReference()),
            $courrier
        );

        return $transmission;
    }

    public function receptionner(Transmission $transmission, User $destinataire, ?string $commentaire = null): void
    {
        if ($transmission->getDestinataire()?->getId() !== $destinataire->getId()) {
            throw new BadRequestHttpException('Seul le destinataire peut réceptionner cette transmission.');
        }

        if ($transmission->getEtat() !== TransmissionEtat::Envoye
            || $transmission->getCourrier()?->getStatut() !== CourrierStatut::EnTransmission) {
            throw new BadRequestHttpException('Cette transmission ne peut plus être réceptionnée.');
        }

        $transmission->setEtat(TransmissionEtat::Recu);
        $transmission->setDateReception(new \DateTimeImmutable());
        if ($commentaire) {
            $transmission->setCommentaire(trim(($transmission->getCommentaire() ?? '')."\nRéception: ".$commentaire));
        }

        $this->changerStatut($transmission->getCourrier(), CourrierStatut::Recu, $destinataire, 'Réception confirmée');
    }

    /** @param list<CourrierStatut> $autorises */
    private function assurerTransition(Courrier $courrier, array $autorises): void
    {
        if (!\in_array($courrier->getStatut(), $autorises, true)) {
            throw new BadRequestHttpException(sprintf(
                'Transition impossible depuis le statut « %s ».',
                $courrier->getStatut()->label()
            ));
        }
    }
}
