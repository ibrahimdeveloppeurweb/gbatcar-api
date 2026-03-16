<?php

namespace App\Repository\Client;

use App\Entity\Client\Vehicle;
use App\Utils\TypeVariable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vehicle>
 *
 * @method Vehicle|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vehicle|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vehicle[]    findAll()
 * @method Vehicle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VehicleRepository extends ServiceEntityRepository
{
    /** @var TypeVariable $type */
    private $type;

    public function __construct(ManagerRegistry $registry, TypeVariable $type)
    {
        parent::__construct($registry, Vehicle::class);
        $this->type = $type;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Vehicle $entity, bool $flush = true): void
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
    public function remove(Vehicle $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return Vehicle[] Returns an array of Vehicle objects
    //  */

    /**
     * Rapport du catalogue
     * @param object $data
     * @return Vehicle[]
     */
    public function findCatalogByFilters(object $data)
    {
        $query = $this->createQueryBuilder('v');

        // Recherche par mot-clé (marque ou modèle)
        if (isset($data->search) && TypeVariable::is_not_null($data->search) && $data->search) {
            $query = $query
                ->andWhere('LOWER(v.marque) LIKE :search OR LOWER(v.modele) LIKE :search')
                ->setParameter('search', '%' . $this->type->trim(strtolower($data->search)) . '%');
        }

        // Statut
        if (isset($data->status) && TypeVariable::is_not_null($data->status) && $data->status) {
            $query = $query
                ->andWhere('v.statut = :status')
                ->setParameter('status', $this->type->trim($data->status));
        }

        // Année Min
        if (isset($data->yearMin) && TypeVariable::is_not_null($data->yearMin) && $data->yearMin) {
            $query = $query
                ->andWhere('v.annee >= :yearMin')
                ->setParameter('yearMin', (int)$this->type->trim($data->yearMin));
        }

        // Année Max
        if (isset($data->yearMax) && TypeVariable::is_not_null($data->yearMax) && $data->yearMax) {
            $query = $query
                ->andWhere('v.annee <= :yearMax')
                ->setParameter('yearMax', (int)$this->type->trim($data->yearMax));
        }

        // Prix Min
        if (isset($data->priceMin) && TypeVariable::is_not_null($data->priceMin) && $data->priceMin) {
            $query = $query
                ->andWhere('v.prixDeVente >= :priceMin')
                ->setParameter('priceMin', (float)$this->type->trim($data->priceMin));
        }

        // Prix Max
        if (isset($data->priceMax) && TypeVariable::is_not_null($data->priceMax) && $data->priceMax) {
            $query = $query
                ->andWhere('v.prixDeVente <= :priceMax')
                ->setParameter('priceMax', (float)$this->type->trim($data->priceMax));
        }

        $query = $query->orderBy('v.createdAt', 'DESC');

        return $query->getQuery()->getResult();
    }
    //  * @return Vehicle[] Returns an array of Vehicle objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Vehicle
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
