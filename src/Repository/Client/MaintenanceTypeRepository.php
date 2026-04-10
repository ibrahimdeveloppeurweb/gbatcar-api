<?php

namespace App\Repository\Client;

use App\Entity\Client\MaintenanceType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MaintenanceType|null find($id, $lockMode = null, $lockVersion = null)
 * @method MaintenanceType|null findOneBy(array $criteria, array $orderBy = null)
 * @method MaintenanceType[]    findAll()
 * @method MaintenanceType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaintenanceTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MaintenanceType::class);
    }
}