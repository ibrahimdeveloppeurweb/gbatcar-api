<?php

namespace App\Repository\Client;

use App\Entity\Client\Contract;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contract>
 *
 * @method Contract|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contract|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contract[]    findAll()
 * @method Contract[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContractRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contract::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Contract $entity, bool $flush = true): void
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
    public function remove(Contract $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.client', 'cl')
            ->leftJoin('c.vehicle', 'v')
            ->addSelect('cl')
            ->addSelect('v');

        if (!empty($filters['search'])) {
            $search = '%' . mb_strtolower($filters['search']) . '%';
            $qb->andWhere('LOWER(c.reference) LIKE :search OR LOWER(cl.firstName) LIKE :search OR LOWER(cl.lastName) LIKE :search OR LOWER(v.marque) LIKE :search OR LOWER(v.modele) LIKE :search OR LOWER(v.immatriculation) LIKE :search')
                ->setParameter('search', $search);
        }

        if (!empty($filters['status'])) {
            $status = $filters['status'];
            if ($status === 'VALIDÉ') {
                $qb->andWhere('c.status IN (:statuses) OR LOWER(c.status) IN (:lStatuses)')
                    ->setParameter('statuses', ['VALIDÉ', 'ACTIVE', 'EN COURS', 'VALIDATED'])
                    ->setParameter('lStatuses', ['validé', 'active', 'en cours', 'validated']);
            }
            elseif ($status === 'NEW') {
                $qb->andWhere('c.status IN (:statuses) OR LOWER(c.status) IN (:lStatuses)')
                    ->setParameter('statuses', ['NEW', 'PENDING', 'En Attente'])
                    ->setParameter('lStatuses', ['new', 'pending', 'en attente']);
            }
            else {
                $qb->andWhere('c.status = :status OR LOWER(c.status) = :lStatus')
                    ->setParameter('status', $status)
                    ->setParameter('lStatus', mb_strtolower($status));
            }
        }

        if (!empty($filters['paymentStatus'])) {
            $qb->andWhere('c.paymentStatus = :paymentStatus')
                ->setParameter('paymentStatus', $filters['paymentStatus']);
        }

        if (!empty($filters['startDateMin'])) {
            $qb->andWhere('c.startDate >= :startDateMin')
                ->setParameter('startDateMin', new \DateTime($filters['startDateMin']));
        }

        if (!empty($filters['startDateMax'])) {
            $qb->andWhere('c.startDate <= :startDateMax')
                ->setParameter('startDateMax', new \DateTime($filters['startDateMax']));
        }

        if (isset($filters['progressMin'])) {
            // progress = (paidAmount / totalAmount) * 100
            $qb->andWhere('(c.paidAmount / NULLIF(c.totalAmount, 0)) * 100 >= :progressMin')
                ->setParameter('progressMin', $filters['progressMin']);
        }

        if (isset($filters['progressMax'])) {
            $qb->andWhere('(c.paidAmount / NULLIF(c.totalAmount, 0)) * 100 <= :progressMax')
                ->setParameter('progressMax', $filters['progressMax']);
        }

        $qb->orderBy('c.createdAt', 'DESC');

        if (!empty($filters['count']) && is_numeric($filters['count'])) {
            $qb->setMaxResults((int)$filters['count']);
        }
        else {
            $qb->setMaxResults(20);
        }

        return $qb->getQuery()->getResult();
    }

    public function findLateContracts(array $filters): array
    {
        $today = new \DateTimeImmutable('today');

        $qb = $this->createQueryBuilder('c')
            ->innerJoin('c.paymentSchedules', 'ps')
            ->leftJoin('c.client', 'cl')
            ->leftJoin('c.vehicle', 'v')
            ->leftJoin('v.brand', 'b')
            ->leftJoin('v.vehicleModel', 'vm')
            ->addSelect('cl')
            ->addSelect('v')
            ->addSelect('b')
            ->addSelect('vm')
            ->andWhere('ps.status IN (:lateStatuses)')
            ->andWhere('ps.expectedDate < :today')
            ->setParameter('lateStatuses', ['En retard', 'Partiel'])
            ->setParameter('today', $today);

        // Security: Exclude non-active or suspended contracts for recovery view
        $qb->andWhere('c.status NOT IN (:excludedStatuses)')
            ->setParameter('excludedStatuses', ['ANNULÉ', 'SOLDÉ', 'RÉSILIÉ', 'EXPIRED']);

        if (!empty($filters['search'])) {
            $search = '%' . mb_strtolower($filters['search']) . '%';
            $qb->andWhere('LOWER(c.reference) LIKE :search OR LOWER(cl.firstName) LIKE :search OR LOWER(cl.lastName) LIKE :search OR LOWER(v.immatriculation) LIKE :search')
                ->setParameter('search', $search);
        }

        // IMPORTANT: We want distinct contracts
        $qb->groupBy('c.id');
        $qb->orderBy('c.id', 'DESC');

        if (!empty($filters['count']) && is_numeric($filters['count'])) {
            $qb->setMaxResults((int)$filters['count']);
        }

        return $qb->getQuery()->getResult();
    }
}