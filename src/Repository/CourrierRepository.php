<?php

namespace App\Repository;

use App\Entity\Courrier;
use App\Enum\CourrierStatut;
use App\Enum\CourrierType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Courrier>
 */
class CourrierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Courrier::class);
    }

    /**
     * @param array{
     *     reference?: ?string,
     *     objet?: ?string,
     *     type?: ?string,
     *     expediteur?: ?string,
     *     destinataire?: ?string,
     *     service?: ?int,
     *     statut?: ?string,
     *     priorite?: ?string,
     *     date_debut?: ?string,
     *     date_fin?: ?string,
     *     archives?: bool
     * } $criteria
     *
     * @return list<Courrier>
     */
    public function search(array $criteria, bool $includeArchives = false): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.serviceOrigine', 'so')->addSelect('so')
            ->leftJoin('c.serviceDestinataire', 'sd')->addSelect('sd')
            ->leftJoin('c.createur', 'cr')->addSelect('cr')
            ->orderBy('c.dateEnregistrement', 'DESC');

        if (!empty($criteria['archives'])) {
            $qb->andWhere('c.statut = :archive')->setParameter('archive', CourrierStatut::Archive);
        } elseif (!$includeArchives) {
            $qb->andWhere('c.statut != :archive')->setParameter('archive', CourrierStatut::Archive);
        }

        if (!empty($criteria['reference'])) {
            $qb->andWhere('c.reference LIKE :reference')
                ->setParameter('reference', '%'.$criteria['reference'].'%');
        }

        if (!empty($criteria['objet'])) {
            $qb->andWhere('c.objet LIKE :objet')
                ->setParameter('objet', '%'.$criteria['objet'].'%');
        }

        if (!empty($criteria['type'])) {
            $qb->andWhere('c.type = :type')->setParameter('type', $criteria['type']);
        }

        if (!empty($criteria['expediteur'])) {
            $qb->andWhere('c.expediteur LIKE :expediteur')
                ->setParameter('expediteur', '%'.$criteria['expediteur'].'%');
        }

        if (!empty($criteria['destinataire'])) {
            $qb->andWhere('c.destinataire LIKE :destinataire')
                ->setParameter('destinataire', '%'.$criteria['destinataire'].'%');
        }

        if (!empty($criteria['service'])) {
            $qb->andWhere('c.serviceOrigine = :service OR c.serviceDestinataire = :service')
                ->setParameter('service', $criteria['service']);
        }

        if (!empty($criteria['statut'])) {
            $qb->andWhere('c.statut = :statut')->setParameter('statut', $criteria['statut']);
        }

        if (!empty($criteria['priorite'])) {
            $qb->andWhere('c.priorite = :priorite')->setParameter('priorite', $criteria['priorite']);
        }

        if (!empty($criteria['date_debut'])) {
            $qb->andWhere('c.dateEnregistrement >= :dateDebut')
                ->setParameter('dateDebut', new \DateTimeImmutable($criteria['date_debut'].' 00:00:00'));
        }

        if (!empty($criteria['date_fin'])) {
            $qb->andWhere('c.dateEnregistrement <= :dateFin')
                ->setParameter('dateFin', new \DateTimeImmutable($criteria['date_fin'].' 23:59:59'));
        }

        return $qb->getQuery()->getResult();
    }

    /** @return list<Courrier> */
    public function findRecents(int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.statut != :archive')
            ->setParameter('archive', CourrierStatut::Archive)
            ->orderBy('c.dateEnregistrement', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countByStatut(CourrierStatut $statut): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.statut = :statut')
            ->setParameter('statut', $statut)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countNonArchives(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.statut != :archive')
            ->setParameter('archive', CourrierStatut::Archive)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return array<string, int> */
    public function statsParStatut(): array
    {
        $rows = $this->createQueryBuilder('c')
            ->select('c.statut AS statut, COUNT(c.id) AS total')
            ->groupBy('c.statut')
            ->getQuery()
            ->getArrayResult();

        $stats = [];
        foreach ($rows as $row) {
            $key = $row['statut'] instanceof CourrierStatut ? $row['statut']->value : (string) $row['statut'];
            $stats[$key] = (int) $row['total'];
        }

        return $stats;
    }

    /** @return array<string, int> */
    public function statsParType(): array
    {
        $rows = $this->createQueryBuilder('c')
            ->select('c.type AS type, COUNT(c.id) AS total')
            ->groupBy('c.type')
            ->getQuery()
            ->getArrayResult();

        $stats = [];
        foreach ($rows as $row) {
            $key = $row['type'] instanceof CourrierType ? $row['type']->value : (string) $row['type'];
            $stats[$key] = (int) $row['total'];
        }

        return $stats;
    }

    public function generateNextReference(string $prefix): string
    {
        $year = (new \DateTimeImmutable())->format('Y');
        $pattern = sprintf('%s-%s-%%', strtoupper($prefix), $year);

        $last = $this->createQueryBuilder('c')
            ->andWhere('c.reference LIKE :pattern')
            ->setParameter('pattern', $pattern)
            ->orderBy('c.reference', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $seq = 1;
        if ($last instanceof Courrier) {
            $parts = explode('-', $last->getReference());
            $seq = ((int) end($parts)) + 1;
        }

        return sprintf('%s-%s-%04d', strtoupper($prefix), $year, $seq);
    }
}
