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
            ->addSelect('c', 'ct');

        if ($search) {
            $qb->andWhere('p.reference LIKE :search OR c.lastName LIKE :search OR c.firstName LIKE :search OR ct.reference LIKE :search')
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

        return $qb->orderBy('p.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}