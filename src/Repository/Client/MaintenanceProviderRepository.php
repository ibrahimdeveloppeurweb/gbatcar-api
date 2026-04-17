<?php

namespace App\Repository\Client;

use App\Entity\Client\MaintenanceProvider;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MaintenanceProvider|null find($id, $lockMode = null, $lockVersion = null)
 * @method MaintenanceProvider|null findOneBy(array $criteria, array $orderBy = null)
 * @method MaintenanceProvider[]    findAll()
 * @method MaintenanceProvider[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaintenanceProviderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MaintenanceProvider::class);
    }
}