<?php

namespace App\Repository\Client;

use App\Entity\Client\Payment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Payment>
 *
 * @method Payment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Payment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Payment[]    findAll()
 * @method Payment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Payment $entity, bool $flush = true): void
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
    public function remove(Payment $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findByFilters(array $filters): array
    {
        $limit = isset($filters['count']) ? (int)$filters['count'] : 20;
        $search = $filters['search'] ?? null;
        $method = $filters['method'] ?? null;
        $status = $filters['status'] ?? null;
        $dateMin = $filters['dateMin'] ?? null;
        $dateMax = $filters['dateMax'] ?? null;
        $amountMin = $filters['amountMin'] ?? null;
        $amountMax = $filters['amountMax'] ?? null;

        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.client', 'c')
            ->leftJoin('p.contract', 'ct')
            ->leftJoin('ct.vehicle', 'vDirect')
            ->leftJoin('ct.vehicleDemands', 'vd')
            ->leftJoin('vd.assignedVehicles', 'vAssigned')
            ->addSelect('c', 'ct', 'vDirect', 'vd', 'vAssigned');

        if ($search) {
            $qb->andWhere('p.reference LIKE :search OR p.observation LIKE :search OR c.lastName LIKE :search OR c.firstName LIKE :search OR ct.reference LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($method) {
            $qb->andWhere('p.method = :method')
                ->setParameter('method', $method);
        }

        if ($status) {
            $qb->andWhere('p.status = :status')
                ->setParameter('status', $status);
        }

        if ($dateMin) {
            $qb->andWhere('p.date >= :dateMin')
                ->setParameter('dateMin', $dateMin);
        }

        if ($dateMax) {
            $qb->andWhere('p.date <= :dateMax')
                ->setParameter('dateMax', $dateMax);
        }

        if ($amountMin !== null) {
            $qb->andWhere('p.amount >= :amountMin')
                ->setParameter('amountMin', $amountMin);
        }

        if ($amountMax !== null) {
            $qb->andWhere('p.amount <= :amountMax')
                ->setParameter('amountMax', $amountMax);
        }

        // Prioritized Vehicle Filter (Consolidation of vehicleId and vehicle UUID/Plate)
        if (isset($filters['vehicleId']) || isset($filters['vehicle'])) {
            $vIds = [];
            if (isset($filters['vehicleId']) && !empty($filters['vehicleId'])) {
                // Technical ID is prioritized
                $vIds = [$filters['vehicleId']];
            } else {
                // Fallback to UUID/Plate identification
                $vSearch = trim($filters['vehicle']);
                $cleanVId = str_replace('/', '', $vSearch);
                $vRes = $this->_em->getRepository(\App\Entity\Client\Vehicle::class)
                    ->createQueryBuilder('v_sub')
                    ->select('v_sub.id')
                    ->where('v_sub.uuid = :vE OR v_sub.immatriculation = :vE OR v_sub.uuid LIKE :vL OR v_sub.immatriculation LIKE :vL')
                    ->setParameter('vE', $vSearch)
                    ->setParameter('vL', '%' . $cleanVId . '%')
                    ->getQuery()
                    ->getScalarResult();
                $vIds = array_column($vRes, 'id');
            }

            if (!empty($vIds)) {
                // Find all Contracts related to these vehicles
                $cIds = $this->_em->getRepository(\App\Entity\Client\Contract::class)
                    ->createQueryBuilder('ct_sub')
                    ->select('ct_sub.id')
                    ->leftJoin('ct_sub.vehicleDemands', 'vd_sub')
                    ->leftJoin('vd_sub.assignedVehicles', 'vas_sub')
                    ->where('ct_sub.vehicle IN (:vs) OR vas_sub.id IN (:vs)')
                    ->setParameter('vs', $vIds)
                    ->getQuery()
                    ->getScalarResult();
                $cIds = array_column($cIds, 'id');

                if (!empty($cIds)) {
                    $qb->andWhere('p.contract IN (:cts)')
                        ->setParameter('cts', $cIds);
                } else {
                    $qb->andWhere('1 = 0'); // Contracts not found
                }
            } else {
                $qb->andWhere('1 = 0'); // Vehicles not found
            }
        }

        return $qb->orderBy('p.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}