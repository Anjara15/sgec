<?php

namespace App\Repository;

use App\Entity\JournalActivite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JournalActivite>
 */
class JournalActiviteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JournalActivite::class);
    }

    /** @return list<JournalActivite> */
    public function findRecents(int $limit = 20): array
    {
        return $this->createQueryBuilder('j')
            ->leftJoin('j.utilisateur', 'u')->addSelect('u')
            ->orderBy('j.date', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
