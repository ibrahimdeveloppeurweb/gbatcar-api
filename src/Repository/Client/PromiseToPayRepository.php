<?php

namespace App\Repository\Client;

use App\Entity\Client\PromiseToPay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PromiseToPay>
 */
class PromiseToPayRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PromiseToPay::class);
    }

    public function findPendingPromisesCount(): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.status = :status')
            ->setParameter('status', PromiseToPay::STATUS['PENDING'])
            ->getQuery()
            ->getSingleScalarResult();
    }
}
