<?php

namespace App\Repository\Client;

use App\Entity\Client\MaintenanceAlert;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MaintenanceAlert>
 *
 * @method MaintenanceAlert|null find($id, $lockMode = null, $lockVersion = null)
 * @method MaintenanceAlert|null findOneBy(array $criteria, array $orderBy = null)
 * @method MaintenanceAlert[]    findAll()
 * @method MaintenanceAlert[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaintenanceAlertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MaintenanceAlert::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(MaintenanceAlert $entity, bool $flush = true): void
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
    public function remove(MaintenanceAlert $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return MaintenanceAlert[] Returns an array of MaintenanceAlert objects
    //  */
    /*
     public function findByExampleField($value)
     {
     return $this->createQueryBuilder('m')
     ->andWhere('m.exampleField = :val')
     ->setParameter('val', $value)
     ->orderBy('m.id', 'ASC')
     ->setMaxResults(10)
     ->getQuery()
     ->getResult()
     ;
     }
     */

    public function findOneByUuid($uuid): ?MaintenanceAlert
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.uuid = :val')
            ->setParameter('val', $uuid, 'uuid')
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function countByYear(string $year): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.reference LIKE :ref')
            ->setParameter('ref', 'ALT-' . $year . '-%')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findByFilters(array $filters = []): array
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.vehicle', 'v')
            ->leftJoin('m.client', 'c')
            ->addSelect('v')
            ->addSelect('c')
            ->orderBy('m.date', 'DESC');

        $cleanValue = function ($val) {
            return ($val === 'null' || $val === 'undefined' || $val === '') ? null : $val;
        };

        $search = $cleanValue($filters['search'] ?? null);
        $severity = $cleanValue($filters['severity'] ?? null);
        $status = $cleanValue($filters['status'] ?? null);
        $dateMin = $cleanValue($filters['dateMin'] ?? null);
        $dateMax = $cleanValue($filters['dateMax'] ?? null);
        $limit = $cleanValue($filters['limit'] ?? null);

        if ($search) {
            $searchParam = '%' . $search . '%';
            $qb->andWhere(
                $qb->expr()->orX(
                $qb->expr()->like('m.type', ':search'),
                $qb->expr()->like('m.description', ':search'),
                $qb->expr()->like('v.immatriculation', ':search'),
                $qb->expr()->like('v.marque', ':search'),
                $qb->expr()->like('v.modele', ':search'),
                $qb->expr()->like('c.firstName', ':search'),
                $qb->expr()->like('c.lastName', ':search'),
                $qb->expr()->like('c.name', ':search')
            )
            )->setParameter('search', $searchParam);
        }

        if ($severity) {
            $qb->andWhere('m.severity = :severity')->setParameter('severity', $severity);
        }

        if ($status) {
            $qb->andWhere('m.status = :status')->setParameter('status', $status);
        }

        if ($dateMin) {
            $qb->andWhere('m.date >= :dateMin')
                ->setParameter('dateMin', new \DateTimeImmutable($dateMin));
        }

        if ($dateMax) {
            $qb->andWhere('m.date <= :dateMax')
                ->setParameter('dateMax', new \DateTimeImmutable($dateMax . ' 23:59:59'));
        }

        if ($limit) {
            $qb->setMaxResults((int)$limit);
        }

        return $qb->getQuery()->getResult();
    }
}