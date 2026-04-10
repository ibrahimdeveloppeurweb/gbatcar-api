<?php

namespace App\Repository\Client;

use App\Entity\Client\MaintenanceBudget;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MaintenanceBudget>
 *
 * @method MaintenanceBudget|null find($id, $lockMode = null, $lockVersion = null)
 * @method MaintenanceBudget|null findOneBy(array $criteria, array $orderBy = null)
 * @method MaintenanceBudget[]    findAll()
 * @method MaintenanceBudget[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaintenanceBudgetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MaintenanceBudget::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(MaintenanceBudget $entity, bool $flush = true): void
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
    public function remove(MaintenanceBudget $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Get budgets for a list of periods
     */
    public function findByPeriods(array $periods): array
    {
        $results = $this->createQueryBuilder('b')
            ->where('b.period IN (:periods)')
            ->setParameter('periods', $periods)
            ->getQuery()
            ->getResult();

        $map = [];
        foreach ($results as $budget) {
            $map[$budget->getPeriod()] = $budget->getAmount();
        }
        return $map;
    }
}