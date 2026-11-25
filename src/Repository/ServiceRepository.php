<?php

namespace App\Repository;

use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Service>
 */
class ServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Service::class);
    }

    /** @return list<Service> */
    public function findActifs(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.statut = :statut')
            ->setParameter('statut', 'actif')
            ->orderBy('s.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
