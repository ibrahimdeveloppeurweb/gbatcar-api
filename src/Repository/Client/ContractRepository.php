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
            $qb->andWhere('c.status = :status')
                ->setParameter('status', $filters['status']);
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
}