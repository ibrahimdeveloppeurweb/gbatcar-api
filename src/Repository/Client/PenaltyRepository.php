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

        if (isset($data->search) && $data->search) {
            $qb->leftJoin('p.client', 'search_cl')
                ->andWhere('p.reference LIKE :search OR p.reason LIKE :search OR search_cl.libelle LIKE :search')
                ->setParameter('search', '%' . $data->search . '%');
        }

        if (isset($data->status) && $data->status) {
            $qb->andWhere('p.status = :status')
                ->setParameter('status', $data->status);
        }

        if (isset($data->severity) && $data->severity) {
            $qb->andWhere('p.severity = :severity')
                ->setParameter('severity', $data->severity);
        }

        if (isset($data->dateMin) && $data->dateMin) {
            $qb->andWhere('p.date >= :dateMin')
                ->setParameter('dateMin', new \DateTimeImmutable($data->dateMin));
        }

        if (isset($data->dateMax) && $data->dateMax) {
            $qb->andWhere('p.date <= :dateMax')
                ->setParameter('dateMax', new \DateTimeImmutable($data->dateMax));
        }

        $qb->orderBy('p.date', 'DESC')
            ->addOrderBy('p.id', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function findPendingByContract($contract)
    {
        return $this->createQueryBuilder('p')
            ->where('p.contract = :contract')
            ->andWhere('p.status IN (:statuses)')
            ->setParameter('contract', $contract)
            ->setParameter('statuses', ['PENDING', 'PARTIAL', 'En attente'])
            ->orderBy('p.date', 'ASC')
            ->getQuery()
            ->getResult();
    }
}