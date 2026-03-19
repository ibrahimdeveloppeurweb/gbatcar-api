<?php

namespace App\Repository\Extra;

use App\Entity\Extra\GeneralSettingHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GeneralSettingHistory>
 *
 * @method GeneralSettingHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method GeneralSettingHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method GeneralSettingHistory[]    findAll()
 * @method GeneralSettingHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GeneralSettingHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GeneralSettingHistory::class);
    }
}