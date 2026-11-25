<?php

namespace App\Repository;

use App\Entity\Transmission;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transmission>
 */
class TransmissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transmission::class);
    }

    /** @return list<Transmission> */
    public function findPourUtilisateur(User $user): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.expediteur = :user OR t.destinataire = :user')
            ->setParameter('user', $user)
            ->orderBy('t.dateTransmission', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
