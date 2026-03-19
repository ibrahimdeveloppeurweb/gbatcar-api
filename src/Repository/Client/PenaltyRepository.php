<?php

namespace App\Repository\Client;

use App\Entity\Client\Penalty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Penalty>
 *
 * @method Penalty|null find($id, $lockMode = null, $lockVersion = null)
 * @method Penalty|null findOneBy(array $criteria, array $orderBy = null)
 * @method Penalty[]    findAll()
 * @method Penalty[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PenaltyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Penalty::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Penalty $entity, bool $flush = true): void
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
    public function remove(Penalty $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param object $data
     * @return Penalty[]
     */
    public function findByFilters(object $data)
    {
        $qb = $this->createQueryBuilder('p');

        if (isset($data->vehicleUuid) && $data->vehicleUuid) {
            $qb->join('p.vehicle', 'v')
                ->andWhere('v.uuid = :vehicleUuid')
                ->setParameter('vehicleUuid', $data->vehicleUuid);
        }

        if (isset($data->contractUuid) && $data->contractUuid) {
            $qb->join('p.contract', 'c')
                ->andWhere('c.uuid = :contractUuid')
                ->setParameter('contractUuid', $data->contractUuid);
        }

        if (isset($data->clientUuid) && $data->clientUuid) {
            $qb->join('p.client', 'cl')
                ->andWhere('cl.uuid = :clientUuid')
                ->setParameter('clientUuid', $data->clientUuid);
        }

        $qb->orderBy('p.date', 'DESC');

        return $qb->getQuery()->getResult();
    }
}