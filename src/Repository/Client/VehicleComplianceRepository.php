<?php

namespace App\Repository\Client;

use App\Entity\Client\VehicleCompliance;
use App\Utils\TypeVariable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VehicleCompliance>
 *
 * @method VehicleCompliance|null find($id, $lockMode = null, $lockVersion = null)
 * @method VehicleCompliance|null findOneBy(array $criteria, array $orderBy = null)
 * @method VehicleCompliance[]    findAll()
 * @method VehicleCompliance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VehicleComplianceRepository extends ServiceEntityRepository
{
    /** @var TypeVariable $type */
    private $type;

    public function __construct(ManagerRegistry $registry, TypeVariable $type)
    {
        parent::__construct($registry, VehicleCompliance::class);
        $this->type = $type;
    }

    /**
     * @param object $data
     * @return VehicleCompliance[]
     */
    public function findByFilters(object $data)
    {
        $query = $this->createQueryBuilder('c')
            ->leftJoin('c.vehicle', 'v');

        // Recherche par véhicule (marque, modèle ou plaque)
        if (isset($data->search) && TypeVariable::is_not_null($data->search) && $data->search) {
            $query = $query
                ->andWhere('LOWER(v.marque) LIKE :search OR LOWER(v.modele) LIKE :search OR LOWER(v.immatriculation) LIKE :search')
                ->setParameter('search', '%' . $this->type->trim(strtolower($data->search)) . '%');
        }

        // Filtrage par véhicule spécifique
        if (isset($data->vehicleId) && TypeVariable::is_not_null($data->vehicleId) && $data->vehicleId) {
            $query = $query
                ->andWhere('v.id = :vehicleId OR v.uuid = :vehicleId')
                ->setParameter('vehicleId', $this->type->trim($data->vehicleId));
        }

        // Filtrage par plage de dates d'expiration (sur tous les types de documents)
        if (isset($data->startDate) && $data->startDate) {
            $query = $query
                ->andWhere('(c.assuranceExpiryDate >= :startDate OR c.technicalInspectionExpiryDate >= :startDate OR c.roadTaxExpiryDate >= :startDate OR c.transportLicenseExpiryDate >= :startDate OR c.fireExtinguisherExpiryDate >= :startDate OR c.carteGriseExpiryDate >= :startDate OR c.leaseContractExpiryDate >= :startDate)')
                ->setParameter('startDate', new \DateTime($data->startDate));
        }

        if (isset($data->endDate) && $data->endDate) {
            $query = $query
                ->andWhere('(c.assuranceExpiryDate <= :endDate OR c.technicalInspectionExpiryDate <= :endDate OR c.roadTaxExpiryDate <= :endDate OR c.transportLicenseExpiryDate <= :endDate OR c.fireExtinguisherExpiryDate <= :endDate OR c.carteGriseExpiryDate <= :endDate OR c.leaseContractExpiryDate <= :endDate)')
                ->setParameter('endDate', new \DateTime($data->endDate));
        }

        $query = $query->orderBy('c.createdAt', 'DESC');

        return $query->getQuery()->getResult();
    }
}