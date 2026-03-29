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
}
