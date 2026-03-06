<?php

namespace App\Repository\Extra;

use App\Entity\Extra\NotificationSetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NotificationSetting>
 *
 * @method NotificationSetting|null find($id, $lockMode = null, $lockVersion = null)
 * @method NotificationSetting|null findOneBy(array $criteria, array $orderBy = null)
 * @method NotificationSetting[]    findAll()
 * @method NotificationSetting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationSettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationSetting::class);
    }

    public function add(NotificationSetting $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(NotificationSetting $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByUuid($uuid): ?NotificationSetting
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.uuid = :val')
            ->setParameter('val', $uuid)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
}