<?php

namespace App\Repository\Client;

use App\Entity\Client\Vehicle;
use App\Entity\Client\Contract;
use App\Entity\Client\ContractVehicleDemand;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * @method Vehicle|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vehicle|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vehicle[]    findAll()
 * @method Vehicle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VehicleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicle::class);
    }

    /**
     * @return Vehicle[] Returns an array of Vehicle objects
     */
    public function findCatalogByFilters($filters = [])
    {
        // Extraction des filtres en castant vers un tableau
        if (is_object($filters)) {
            $filters = (array)$filters;
        }

        $cleanValue = function ($val) {
            return ($val === 'null' || $val === 'undefined' || $val === '') ? null : $val;
        };

        $search = $cleanValue($filters['search'] ?? null);
        $status = $cleanValue($filters['status'] ?? null);
        $assignedClient = $cleanValue($filters['assignedClient'] ?? null);
        $paymentStatus = $cleanValue($filters['paymentStatus'] ?? null);
        $yearMin = $cleanValue($filters['yearMin'] ?? null);
        $yearMax = $cleanValue($filters['yearMax'] ?? null);
        $mileageMin = $cleanValue($filters['mileageMin'] ?? null);
        $mileageMax = $cleanValue($filters['mileageMax'] ?? null);
        $limit = $filters['limit'] ?? 10;

        $activeStatusesList = "('ACTIVE', 'EN COURS', 'EN_COURS', 'VALIDÉ', 'Actif', 'En cours', 'Validé')";

        $query = $this->createQueryBuilder('v')
            ->leftJoin('v.client', 'c')
            ->leftJoin('v.contracts', 'ctr', 'WITH', 'ctr.status IN ' . $activeStatusesList)
            ->leftJoin('v.vehicleDemands', 'vd')
            ->leftJoin('vd.contract', 'ctrf', 'WITH', 'ctrf.status IN ' . $activeStatusesList)
            ->where('v.deletedAt IS NULL');

        if ($search && is_string($search)) {
            $query->andWhere('v.immatriculation LIKE :search OR v.marque LIKE :search OR v.modele LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($status && is_string($status)) {
            $query->andWhere('v.statut = :status')
                ->setParameter('status', $status);
        }

        if ($assignedClient && is_string($assignedClient)) {
            if ($assignedClient === 'Aucun') {
                $query->andWhere('c.id IS NULL AND vd.id IS NULL');
            }
            else {
                $query->andWhere('c.firstName LIKE :client OR c.lastName LIKE :client OR c.name LIKE :client')
                    ->setParameter('client', '%' . $assignedClient . '%');
            }
        }

        if ($paymentStatus && is_string($paymentStatus)) {
            $activeStatuses = ['ACTIVE', 'EN COURS', 'EN_COURS', 'VALIDÉ', 'Actif', 'En cours', 'Validé'];

            // Subquery for late count
            $lateCountSql = "(SELECT COUNT(ps_sub.id) FROM App\Entity\Client\PaymentSchedule ps_sub 
                             WHERE ps_sub.contract = COALESCE(ctr.id, ctrf.id) 
                             AND ps_sub.expectedDate < CURRENT_DATE() 
                             AND ps_sub.status IN ('En retard', 'Partiel'))";

            if ($paymentStatus === 'À jour') {
                $query->andWhere("(ctr.id IS NOT NULL OR ctrf.id IS NOT NULL)")
                    ->andWhere($lateCountSql . " = 0");
            }
            elseif ($paymentStatus === 'En retard') {
                $query->andWhere($lateCountSql . " >= 1")
                    ->andWhere(str_replace('ps_sub', 'ps_sub2', $lateCountSql) . " <= 5");
            }
            elseif ($paymentStatus === 'Critique') {
                $query->andWhere($lateCountSql . " >= 6");
            }
            elseif ($paymentStatus === 'Soldé') {
                $query->andWhere('ctr.status IN (:term) OR ctrf.status IN (:term) OR v.statut = :vendu')
                    ->setParameter('term', ['TERMINÉ', 'SOLDÉ', 'Vendu', 'Solder'])
                    ->setParameter('vendu', 'Vendu');
            }
        }

        if ($yearMin) {
            $query->andWhere('v.annee >= :yearMin')
                ->setParameter('yearMin', $yearMin);
        }
        if ($yearMax) {
            $query->andWhere('v.annee <= :yearMax')
                ->setParameter('yearMax', $yearMax);
        }
        if ($mileageMin) {
            $query->andWhere('v.kilometrage >= :mileageMin')
                ->setParameter('mileageMin', $mileageMin);
        }
        if ($mileageMax) {
            $query->andWhere('v.kilometrage <= :mileageMax')
                ->setParameter('mileageMax', $mileageMax);
        }

        $query->orderBy('v.createdAt', 'DESC');
        $query->setMaxResults($limit);
        $query->groupBy('v.id');

        return $query->getQuery()->getResult();
    }

    public function findByTab($tab = 'all', $limit = 10, $search = null, $status = null)
    {
        $query = $this->createQueryBuilder('v')
            ->where('v.deletedAt IS NULL');

        if ($search) {
            $query->andWhere('v.immatriculation LIKE :search OR v.marque LIKE :search OR v.modele LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($status) {
            $query->andWhere('v.statut = :status')
                ->setParameter('status', $status);
        }

        $pStatus = null;
        switch ($tab) {
            case 'ok':
                $pStatus = 'À jour';
                break;
            case 'alert':
                $pStatus = 'En retard';
                break;
            case 'critical':
                $pStatus = 'Critique';
                break;
            case 'finished':
                $pStatus = 'soldé';
                break;
        }

        if ($pStatus) {
            if ($pStatus === 'soldé') {
                $query->leftJoin('v.contracts', 'c_sold')
                    ->leftJoin('v.vehicleDemands', 'vd_sold')
                    ->leftJoin('vd_sold.contract', 'c2_sold');
                $query->andWhere('c_sold.status IN (:term) OR c2_sold.status IN (:term) OR v.statut = :soldStatus')
                    ->setParameter('term', ['TERMINÉ', 'SOLDÉ', 'Vendu', 'Solder'])
                    ->setParameter('soldStatus', 'Vendu');
            }
            else {
                $query->andWhere('v.statut != :venduStat')
                    ->setParameter('venduStat', 'Vendu');

                $activeStatusesList = "('ACTIVE', 'EN COURS', 'EN_COURS', 'VALIDÉ', 'Actif', 'En cours', 'Validé')";
                $query->leftJoin('v.contracts', 'c_f', 'WITH', 'c_f.status IN ' . $activeStatusesList)
                    ->leftJoin('v.vehicleDemands', 'vd_f')
                    ->leftJoin('vd_f.contract', 'c2_f', 'WITH', 'c2_f.status IN ' . $activeStatusesList);

                $lateCountSql = "(SELECT COUNT(ps_sub.id) FROM App\Entity\Client\PaymentSchedule ps_sub 
                                 WHERE ps_sub.contract = COALESCE(c_f.id, c2_f.id) 
                                 AND ps_sub.expectedDate < CURRENT_DATE() 
                                 AND ps_sub.status IN ('En retard', 'Partiel'))";

                if ($pStatus === 'À jour') {
                    $query->andWhere("(c_f.id IS NOT NULL OR c2_f.id IS NOT NULL)")
                        ->andWhere($lateCountSql . " = 0");
                }
                elseif ($pStatus === 'En retard') {
                    $query->andWhere($lateCountSql . " >= 1")
                        ->andWhere(str_replace('ps_sub', 'ps_sub2', $lateCountSql) . " <= 5");
                }
                elseif ($pStatus === 'Critique') {
                    $query->andWhere($lateCountSql . " >= 6");
                }
            }
        }
        else {
            // "Tous" exclusions
            $query->andWhere('v.statut != :venduStat')
                ->setParameter('venduStat', 'Vendu');
        }

        $query->orderBy('v.id', 'DESC');
        $query->setMaxResults($limit);
        $query->groupBy('v.id');

        return $query->getQuery()->getResult();
    }

    public function getDashboardMetrics()
    {
        $conn = $this->getEntityManager()->getConnection();

        // 1. KPIs Généraux (Single query for performance)
        $sqlKpi = "
            SELECT 
                COUNT(*) as total_fleet,
                COALESCE(SUM(purchase_price), 0) as total_value,
                SUM(CASE WHEN statut = 'Assigné' THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN statut = 'En Maintenance' THEN 1 ELSE 0 END) as maintenance_count,
                SUM(CASE WHEN statut = 'Vendu' THEN 1 ELSE 0 END) as sold_count,
                (
                    SELECT COUNT(DISTINCT v2.id)
                    FROM vehicle v2
                    LEFT JOIN contract c2 ON v2.id = c2.vehicle_id AND c2.status IN ('ACTIVE', 'EN COURS', 'EN_COURS', 'VALIDÉ', 'Actif', 'En cours', 'Validé')
                    LEFT JOIN contract_vehicle_demand_vehicle ad ON v2.id = ad.vehicle_id
                    LEFT JOIN contract_vehicle_demand vd ON ad.contract_vehicle_demand_id = vd.id
                    LEFT JOIN contract c3 ON vd.contract_id = c3.id AND c3.status IN ('ACTIVE', 'EN COURS', 'EN_COURS', 'VALIDÉ', 'Actif', 'En cours', 'Validé')
                    WHERE (
                        -- Either direct contract or fleet contract exists
                        (c2.id IS NOT NULL OR c3.id IS NOT NULL)
                        AND 
                        -- AND zero late schedules
                        NOT EXISTS (
                            SELECT 1 FROM payment_schedule ps 
                            WHERE ps.contract_id = COALESCE(c2.id, c3.id)
                            AND ps.expected_date < CURRENT_DATE()
                            AND ps.status IN ('En retard', 'Partiel')
                        )
                    )
                    AND v2.deleted_at IS NULL
                    AND v2.statut != 'Vendu'
                ) as good_payers
            FROM vehicle
            WHERE deleted_at IS NULL
        ";
        $kpis = $conn->executeQuery($sqlKpi)->fetchAssociative();

        // 2. Distribution par Statut
        $sqlDist = "SELECT statut, COUNT(*) as count FROM vehicle WHERE deleted_at IS NULL GROUP BY statut";
        $distribution = $conn->executeQuery($sqlDist)->fetchAllAssociative();

        // 3. Trends (6 derniers mois - simulation as no history table yet)
        $trends = [
            'labels' => ['Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'],
            'data' => [65, 78, 85, 92, 98, 105], // Croissance simulée
            'revenue' => [12000000, 14500000, 16000000, 18500000, 19200000, 21500000],
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
                -- ALERTE PAIEMENT (1 ou 2 retards)
                SELECT 
                    v.id, v.uuid, v.immatriculation, v.marque, v.modele, 
                    c.last_name as client_last_name, c.first_name as client_first_name,
                    CONCAT('Retard de paiement (', 
                        (SELECT COUNT(*) FROM payment_schedule ps WHERE ps.contract_id = COALESCE(ctr.id, ctr2.id) AND ps.expected_date < CURRENT_DATE() AND ps.status IN ('En retard', 'Partiel')),
                        ' échéance(s))'
                    ) as problem,
                    'Attention' as niveau,
                    0 as cost
                FROM vehicle v
                LEFT JOIN client c ON v.client_id = c.id
                LEFT JOIN contract ctr ON v.id = ctr.vehicle_id AND ctr.status IN ('ACTIVE', 'EN COURS', 'EN_COURS', 'VALIDÉ', 'Actif', 'En cours', 'Validé')
                LEFT JOIN contract_vehicle_demand_vehicle ad ON v.id = ad.vehicle_id
                LEFT JOIN contract_vehicle_demand vd ON ad.contract_vehicle_demand_id = vd.id
                LEFT JOIN contract ctr2 ON vd.contract_id = ctr2.id AND ctr2.status IN ('ACTIVE', 'EN COURS', 'EN_COURS', 'VALIDÉ', 'Actif', 'En cours', 'Validé')
                WHERE (
                    SELECT COUNT(*) 
                    FROM payment_schedule ps2 
                    WHERE ps2.contract_id = COALESCE(ctr.id, ctr2.id) 
                    AND ps2.expected_date < CURRENT_DATE() 
                    AND ps2.status IN ('En retard', 'Partiel')
                ) BETWEEN 1 AND 5
                AND v.deleted_at IS NULL
            )
            UNION ALL
            (
                -- ALERTE CRITIQUE (3 retards ou plus)
                SELECT 
                    v.id, v.uuid, v.immatriculation, v.marque, v.modele, 
                    c.last_name as client_last_name, c.first_name as client_first_name,
                    CONCAT('DÉFAUT DE PAIEMENT CRITIQUE (', 
                        (SELECT COUNT(*) FROM payment_schedule ps WHERE ps.contract_id = COALESCE(ctr.id, ctr2.id) AND ps.expected_date < CURRENT_DATE() AND ps.status IN ('En retard', 'Partiel')),
                        ' échéance(s))'
                    ) as problem,
                    'Critique' as niveau,
                    0 as cost
                FROM vehicle v
                LEFT JOIN client c ON v.client_id = c.id
                LEFT JOIN contract ctr ON v.id = ctr.vehicle_id AND ctr.status IN ('ACTIVE', 'EN COURS', 'EN_COURS', 'VALIDÉ', 'Actif', 'En cours', 'Validé')
                LEFT JOIN contract_vehicle_demand_vehicle ad ON v.id = ad.vehicle_id
                LEFT JOIN contract_vehicle_demand vd ON ad.contract_vehicle_demand_id = vd.id
                LEFT JOIN contract ctr2 ON vd.contract_id = ctr2.id AND ctr2.status IN ('ACTIVE', 'EN COURS', 'EN_COURS', 'VALIDÉ', 'Actif', 'En cours', 'Validé')
                WHERE (
                    SELECT COUNT(*) 
                    FROM payment_schedule ps2 
                    WHERE ps2.contract_id = COALESCE(ctr.id, ctr2.id) 
                    AND ps2.expected_date < CURRENT_DATE() 
                    AND ps2.status IN ('En retard', 'Partiel')
                ) >= 6
                AND v.deleted_at IS NULL
            )
            UNION ALL
            (
                SELECT 
                    v.id, v.uuid, v.immatriculation, v.marque, v.modele, 
                    c.last_name as client_last_name, c.first_name as client_first_name,
                    'Alerte Compliance' as problem,
                    'Attention' as niveau,
                    0 as cost
                FROM vehicle v
                LEFT JOIN client c ON v.client_id = c.id
                JOIN vehicle_compliance_document doc ON doc.vehicle_id = v.id
                WHERE doc.end_date <= DATE_ADD(CURRENT_DATE(), INTERVAL 30 DAY)
                AND doc.deleted_at IS NULL
                AND v.deleted_at IS NULL
            )
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

        // 5. Compliance Rate (Simulation derived from docs table)
        $sqlComp = "SELECT COUNT(*) FROM vehicle_compliance_document WHERE end_date > CURRENT_DATE() AND deleted_at IS NULL";
        try {
            $validDocs = (int)$conn->fetchOne($sqlComp);
            $complianceRate = $kpis['total_fleet'] > 0 ? ($validDocs / ($kpis['total_fleet'] * 2)) * 100 : 0; // Avg 2 docs per vehicle
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
}