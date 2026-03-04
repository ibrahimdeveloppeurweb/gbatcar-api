<?php

namespace App\Repository\Admin;

use App\Entity\Admin\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Liste des utilisateurs en fonction du service (ADMIN ou AGENCY)
     * @return User[] Returns an array of User objects
    */
    public function findByService($verif, $service, $agency)
    {
        $query = $this->createQueryBuilder('u');
        if ($verif === "ADMIN") {
            $query = $query->andWhere('u.admin IS NOT NULL');
        } elseif ($verif === "AGENCY" && $agency !== null) {
            $query = $query
                ->andWhere('u.agency IS NOT NULL')
                ->andWhere('u.agency = :agency')
                ->setParameter('agency', $agency)
            ;
        }
        if ($service && $service !== null && $service !== 'null') {
            $query
                ->join('u.service', 's')
                ->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%'.$service.'%')
            ;
        }
        return $query->getQuery()->getResult();
    }

    /**
     * Liste des utilisateurs admin
     * @return User[] Returns an array of User objects
    */
    public function findByAdmin()
    {
        $query = $this->createQueryBuilder('u')
            ->andWhere('u.admin IS NOT NULL')
            ->setMaxResults(20)
        ;
        return $query->getQuery()->getResult();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(User $entity, bool $flush = true): void
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
    public function remove(User $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
