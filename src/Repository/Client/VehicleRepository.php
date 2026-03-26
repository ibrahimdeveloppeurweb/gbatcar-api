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

        // Recherche par mot-clé (marque, modèle ou plaque)
        if (isset($data->search) && TypeVariable::is_not_null($data->search) && $data->search) {
            $query = $query
                ->andWhere('LOWER(v.marque) LIKE :search OR LOWER(v.modele) LIKE :search OR LOWER(v.immatriculation) LIKE :search')
                ->setParameter('search', '%' . $this->type->trim(strtolower($data->search)) . '%');
        }

        // Statut du véhicule
        if (isset($data->status) && TypeVariable::is_not_null($data->status) && $data->status) {
            $query = $query
                ->andWhere('LOWER(v.statut) = :status')
                ->setParameter('status', strtolower($this->type->trim($data->status)));
        }

        // Assignation Client (Improved to support both direct assignment and fleet demands)
        if (isset($data->assignedClient) && TypeVariable::is_not_null($data->assignedClient) && $data->assignedClient) {
            $clientTerm = '%' . strtolower($this->type->trim($data->assignedClient)) . '%';
            $query = $query
                ->leftJoin('v.client', 'c1')
                ->leftJoin('v.vehicleDemands', 'vd')
                ->leftJoin('vd.contract', 'ctr')
                ->leftJoin('ctr.client', 'c2')
                ->andWhere('(LOWER(c1.lastName) LIKE :clientTerm OR LOWER(c1.firstName) LIKE :clientTerm) OR (LOWER(c2.lastName) LIKE :clientTerm OR LOWER(c2.firstName) LIKE :clientTerm)')
                ->setParameter('clientTerm', $clientTerm)
                ->addGroupBy('v.id'); // Vital for PK in most SQL modes
        }

        // Santé Paiement
        if (isset($data->paymentStatus) && TypeVariable::is_not_null($data->paymentStatus) && $data->paymentStatus) {
            $query = $query
                ->andWhere('LOWER(v.paymentStatus) = :paymentStatus')
                ->setParameter('paymentStatus', strtolower($this->type->trim($data->paymentStatus)));
        }

        // Année Min/Max
        if (isset($data->yearMin) && TypeVariable::is_not_null($data->yearMin) && $data->yearMin) {
            $query = $query->andWhere('v.annee >= :yearMin')->setParameter('yearMin', (int)$this->type->trim($data->yearMin));
        }
        if (isset($data->yearMax) && TypeVariable::is_not_null($data->yearMax) && $data->yearMax) {
            $query = $query->andWhere('v.annee <= :yearMax')->setParameter('yearMax', (int)$this->type->trim($data->yearMax));
        }

        // Kilométrage Min/Max
        if (isset($data->mileageMin) && TypeVariable::is_not_null($data->mileageMin) && $data->mileageMin) {
            $query = $query->andWhere('v.kilometrage >= :mileageMin')->setParameter('mileageMin', (int)$this->type->trim($data->mileageMin));
        }
        if (isset($data->mileageMax) && TypeVariable::is_not_null($data->mileageMax) && $data->mileageMax) {
            $query = $query->andWhere('v.kilometrage <= :mileageMax')->setParameter('mileageMax', (int)$this->type->trim($data->mileageMax));
        }

        // Prix Min/Max (Catalog)
        if (isset($data->priceMin) && TypeVariable::is_not_null($data->priceMin) && $data->priceMin) {
            $query = $query->andWhere('v.prixDeVente >= :priceMin')->setParameter('priceMin', (float)$this->type->trim($data->priceMin));
        }
        if (isset($data->priceMax) && TypeVariable::is_not_null($data->priceMax) && $data->priceMax) {
            $query = $query->andWhere('v.prixDeVente <= :priceMax')->setParameter('priceMax', (float)$this->type->trim($data->priceMax));
        }

        // Limit
        if (isset($data->limit) && (int)$data->limit > 0) {
            $query->setMaxResults((int)$data->limit);
        }

        // Filtre spécifique pour n'afficher que les véhicules SANS contrat actif
        if (isset($data->available_only) && $data->available_only == 'true') {
            // Un véhicule est considéré comme disponible s'il n'a AUCUN contrat 
            // avec un statut qui n'est pas "Terminé", "Annulé" ou "Rupture".
            $subQuery = $this->_em->createQueryBuilder()
                ->select('cv.id')
                ->from('App\Entity\Client\Contract', 'c')
                ->join('c.vehicle', 'cv')
                ->where('c.status IN (:active_statuses)')
                ->andWhere('c.deletedAt IS NULL');

            // Si on est en édition, on ignore le contrat actuel pour qu'il ne s'auto-exclue pas
            if (isset($data->current_contract_uuid) && !empty($data->current_contract_uuid)) {
                $subQuery->andWhere('c.uuid != :current_contract_uuid');
                $query->setParameter('current_contract_uuid', $data->current_contract_uuid, 'uuid');
            }

            $query = $query
                ->andWhere($query->expr()->notIn('v.id', $subQuery->getDQL()))
                ->setParameter('active_statuses', ['NEW', 'EN_COURS', 'VALIDÉ']);
        }

        $query = $query->orderBy('v.createdAt', 'DESC');

        return $query->getQuery()->getResult();
    }
    //  * @return Vehicle[] Returns an array of Vehicle objects
//  */
    /**
     * Fetch all aggregated dashboard metrics to avoid N+1 queries.
     */
    public function getDashboardMetrics(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        // 1. KPIs globaux (Total Flotte, Taux Utilisation, En Maintenance)
        $sqlKpi = "
            SELECT 
                COUNT(*) as total_fleet,
                COALESCE(SUM(purchase_price), 0) as total_value,
                SUM(CASE WHEN statut = 'En Location-Vente' THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN statut = 'En Maintenance' THEN 1 ELSE 0 END) as maintenance_count
            FROM vehicle
            -- WHERE deleted_at IS NULL (assuming standard soft delete or direct query)
        ";
        $kpis = $conn->executeQuery($sqlKpi)->fetchAssociative();

        // 2. Répartition Donut
        $sqlDist = "
            SELECT statut, COUNT(*) as count
            FROM vehicle
            GROUP BY statut
        ";
        $distRows = $conn->executeQuery($sqlDist)->fetchAllAssociative();
        $distribution = [];
        foreach ($distRows as $row) {
            $distribution[$row['statut'] ?: 'Inconnu'] = (int)$row['count'];
        }

        // 3. Coûts de maintenance VS Budget (Real Data from 'maintenance' table)
        $sqlMaintenanceTrends = "
            SELECT 
                MONTH(date_intervention) as month, 
                YEAR(date_intervention) as year, 
                SUM(cost) as cost 
            FROM maintenance 
            WHERE date_intervention >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
            AND deleted_at IS NULL
            GROUP BY year, month
        ";
        try {
            $maintenanceTrends = $conn->executeQuery($sqlMaintenanceTrends)->fetchAllAssociative();
        }
        catch (\Exception $e) {
            $maintenanceTrends = [];
        }

        $trends = [
            'maintenance' => $maintenanceTrends,
            'budget' => [] // Kept empty as no budget entity exists yet
        ];

        // 4. Alertes Véhicules (100% Dynamic from DB triggers)
        $sqlAlerts = "
            (
                SELECT 
                    v.id, v.uuid, v.immatriculation, v.marque, v.modele, 
                    c.last_name as client_last_name, c.first_name as client_first_name,
                    'Entretien requis (Kilométrage atteint)' as problem,
                    'Critique' as niveau,
                    0 as cost
                FROM vehicle v
                LEFT JOIN client c ON v.client_id = c.id
                WHERE v.kilometrage >= v.prochain_entretien
                AND v.prochain_entretien IS NOT NULL
                AND v.deleted_at IS NULL
            )
            UNION ALL
            (
                SELECT 
                    v.id, v.uuid, v.immatriculation, v.marque, v.modele, 
                    c.last_name as client_last_name, c.first_name as client_first_name,
                    'Alerte Maintenance' as problem,
                    'Attention' as niveau,
                    0 as cost
                FROM vehicle v
                LEFT JOIN client c ON v.client_id = c.id
                WHERE v.maintenance_alert = 1
                AND (v.kilometrage < v.prochain_entretien OR v.prochain_entretien IS NULL)
                AND v.deleted_at IS NULL
            )
            UNION ALL
            (
                SELECT 
                    v.id, v.uuid, v.immatriculation, v.marque, v.modele, 
                    c.last_name as client_last_name, c.first_name as client_first_name,
                    'Paiement en retard' as problem,
                    'Attention' as niveau,
                    0 as cost
                FROM vehicle v
                LEFT JOIN client c ON v.client_id = c.id
                WHERE v.payment_status = 'En retard'
                AND v.deleted_at IS NULL
            )
            UNION ALL
            (
                SELECT 
                    v.id, v.uuid, v.immatriculation, v.marque, v.modele, 
                    c.last_name as client_last_name, c.first_name as client_first_name,
                    CONCAT(doc.type, CASE WHEN doc.end_date < CURRENT_DATE() THEN ' expirée' ELSE ' expire bientôt' END) as problem,
                    CASE WHEN doc.end_date < CURRENT_DATE() THEN 'Critique' ELSE 'Attention' END as niveau,
                    COALESCE(doc.renewal_cost, 0) as cost
                FROM vehicle_compliance_document doc
                JOIN vehicle v ON doc.vehicle_id = v.id
                LEFT JOIN client c ON v.client_id = c.id
                WHERE doc.end_date <= DATE_ADD(CURRENT_DATE(), INTERVAL 30 DAY)
                AND doc.deleted_at IS NULL
                AND v.deleted_at IS NULL
            )
            LIMIT 10
        ";

        try {
            $alerts = $conn->executeQuery($sqlAlerts)->fetchAllAssociative();

            // Convertir proprement l'UUID binaire envoyé par Doctrine en chaîne Hexadécimale UUID standard
            foreach ($alerts as &$alert) {
                if (isset($alert['uuid']) && is_string($alert['uuid']) && strlen($alert['uuid']) === 16) {
                    $hex = bin2hex($alert['uuid']);
                    $alert['uuid'] = sprintf('%s-%s-%s-%s-%s',
                        substr($hex, 0, 8),
                        substr($hex, 8, 4),
                        substr($hex, 12, 4),
                        substr($hex, 16, 4),
                        substr($hex, 20, 12)
                    );
                }
            }
        }
        catch (\Exception $e) {
            $alerts = [];
        }

        // 5. Conformité Assurance (Dynamic Percentage)
        $sqlCompliance = "
            SELECT COUNT(DISTINCT v.id) as insured_count
            FROM vehicle v
            JOIN vehicle_compliance_document doc ON doc.vehicle_id = v.id
            WHERE LOWER(doc.type) LIKE '%assurance%'
            AND doc.end_date >= CURRENT_DATE()
            AND doc.deleted_at IS NULL
            AND v.deleted_at IS NULL
        ";
        try {
            $insuredCount = (int)$conn->executeQuery($sqlCompliance)->fetchOne();
            $totalVehicles = (int)$kpis['total_fleet'];
            $complianceRate = $totalVehicles > 0 ? round(($insuredCount / $totalVehicles) * 100) : 0;
        }
        catch (\Exception $e) {
            $complianceRate = 0;
        }

        return [
            'kpis' => $kpis,
            'distribution' => $distribution,
            'trends' => $trends,
            'alerts' => $alerts,
            'complianceRate' => $complianceRate
        ];
    }
/*
 public function findByExampleField($value)
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