<?php

namespace App\Repository\Client;

use App\Entity\Client\Maintenance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Maintenance>
 *
 * @method Maintenance|null find($id, $lockMode = null, $lockVersion = null)
 * @method Maintenance|null findOneBy(array $criteria, array $orderBy = null)
 * @method Maintenance[]    findAll()
 * @method Maintenance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaintenanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Maintenance::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Maintenance $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Maintenance $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function getDashboardMetrics(): array
    {
        $qb = $this->createQueryBuilder('m');

        $total = (int)$qb->select('COUNT(m.id)')->getQuery()->getSingleScalarResult();

        $planned = (int)$this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.status = :status')
            ->setParameter('status', 'Planifié')
            ->getQuery()
            ->getSingleScalarResult();

        $inProgress = (int)$this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.status = :status')
            ->setParameter('status', 'En cours')
            ->getQuery()
            ->getSingleScalarResult();

        // Total Cost this month
        $startOfMonth = new \DateTimeImmutable('first day of this month 00:00:00');
        $endOfMonth = new \DateTimeImmutable('last day of this month 23:59:59');

        $totalCost = (float)$this->createQueryBuilder('m')
            ->select('SUM(m.cost)')
            ->where('m.dateIntervention >= :start')
            ->andWhere('m.dateIntervention <= :end')
            ->setParameter('start', $startOfMonth)
            ->setParameter('end', $endOfMonth)
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total' => $total,
            'planned' => $planned,
            'inProgress' => $inProgress,
            'totalCostThisMonth' => $totalCost
        ];
    }

    public function findByFilters(array $filters = []): array
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.vehicle', 'v')
            ->addSelect('v')
            ->orderBy('m.createdAt', 'DESC');

        // Full-text search across reference, type, provider
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $qb->andWhere(
                $qb->expr()->orX(
                $qb->expr()->like('m.reference', ':search'),
                $qb->expr()->like('m.type', ':search'),
                $qb->expr()->like('m.provider', ':search'),
                $qb->expr()->like('v.immatriculation', ':search'),
                $qb->expr()->like('v.marque', ':search'),
                $qb->expr()->like('v.modele', ':search')
            )
            )->setParameter('search', $search);
        }

        if (!empty($filters['status'])) {
            $qb->andWhere('m.status = :status')->setParameter('status', $filters['status']);
        }

        if (!empty($filters['dateMin'])) {
            $qb->andWhere('m.dateIntervention >= :dateMin')
                ->setParameter('dateMin', new \DateTimeImmutable($filters['dateMin']));
        }

        if (!empty($filters['dateMax'])) {
            $qb->andWhere('m.dateIntervention <= :dateMax')
                ->setParameter('dateMax', new \DateTimeImmutable($filters['dateMax'] . ' 23:59:59'));
        }

        if (isset($filters['costMin']) && $filters['costMin'] !== '') {
            $qb->andWhere('m.cost >= :costMin')->setParameter('costMin', (float)$filters['costMin']);
        }

        if (isset($filters['costMax']) && $filters['costMax'] !== '') {
            $qb->andWhere('m.cost <= :costMax')->setParameter('costMax', (float)$filters['costMax']);
        }

        if (!empty($filters['limit'])) {
            $qb->setMaxResults((int)$filters['limit']);
        }

        return $qb->getQuery()->getResult();
    }
}