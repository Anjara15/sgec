<?php

namespace App\Service;

use App\Entity\JournalActivite;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class JournalService
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /** @param array<string, mixed> $infos */
    public function log(?User $user, string $action, string $typeObjet = '', ?int $objetId = null, array $infos = []): void
    {
        $entry = (new JournalActivite())
            ->setUtilisateur($user)
            ->setAction($action)
            ->setTypeObjet($typeObjet)
            ->setObjetId($objetId)
            ->setInfos($infos);

        $this->em->persist($entry);
        $this->em->flush();
    }
}
