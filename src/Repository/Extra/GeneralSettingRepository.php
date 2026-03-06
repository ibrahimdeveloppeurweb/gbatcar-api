<?php

namespace App\Repository\Extra;

use App\Entity\Extra\GeneralSetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GeneralSetting>
 *
 * @method GeneralSetting|null find($id, $lockMode = null, $lockVersion = null)
 * @method GeneralSetting|null findOneBy(array $criteria, array $orderBy = null)
 * @method GeneralSetting[]    findAll()
 * @method GeneralSetting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GeneralSettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GeneralSetting::class);
    }
}