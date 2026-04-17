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
        $currentContractUuid = $cleanValue($filters['current_contract_uuid'] ?? null);
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

        $assignedVehicleId = null;
        if ($currentContractUuid) {
            $cTmp = $this->getEntityManager()->getRepository(Contract::class)->findOneBy(['uuid' => $currentContractUuid]);
            if ($cTmp && $cTmp->getVehicle()) {
                $assignedVehicleId = $cTmp->getVehicle()->getId();
            }
        }

        if ($status && is_string($status)) {
            if ($assignedVehicleId) {
                $query->andWhere('(v.statut = :status OR v.id = :assignedVehicleId)')
                    ->setParameter('assignedVehicleId', $assignedVehicleId);
            }
            else {
                $query->andWhere('v.statut = :status');
            }
            $query->setParameter('status', $status);
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
                $query->leftJoin('v.complianceDocuments', 'vcd_crit', 'WITH', 'vcd_crit.endDate < CURRENT_DATE() AND vcd_crit.deletedAt IS NULL');
                $query->leftJoin('v.maintenances', 'vm_crit', 'WITH', 'vm_crit.status IN (:m_crit_status) AND DATE_DIFF(CURRENT_DATE(), vm_crit.startDate) > 3 AND vm_crit.deletedAt IS NULL');

                $query->andWhere(
                    $query->expr()->orX(
                    $lateCountSql . " >= 6",
                    "v.kilometrage >= v.prochainEntretien",
                    "vcd_crit.id IS NOT NULL",
                    "vm_crit.id IS NOT NULL"
                )
                )->setParameter('m_crit_status', ['EN COURS', 'En cours', 'EN_PROGRESS']);
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
                    $query->leftJoin('v.complianceDocuments', 'vcd_tab_crit', 'WITH', 'vcd_tab_crit.endDate < CURRENT_DATE() AND vcd_tab_crit.deletedAt IS NULL');
                    $query->leftJoin('v.maintenances', 'vm_tab_crit', 'WITH', 'vm_tab_crit.status IN (:m_tab_crit_status) AND DATE_DIFF(CURRENT_DATE(), vm_tab_crit.startDate) > 3 AND vm_tab_crit.deletedAt IS NULL');

                    $query->andWhere(
                        $query->expr()->orX(
                        $lateCountSql . " >= 6",
                        "v.kilometrage >= v.prochainEntretien",
                        "vcd_tab_crit.id IS NOT NULL",
                        "vm_tab_crit.id IS NOT NULL"
                    )
                    )->setParameter('m_tab_crit_status', ['EN COURS', 'En cours', 'EN_PROGRESS']);
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

    public function getDashboardMetrics(int $months = 6)
    {
        $conn = $this->getEntityManager()->getConnection();

        // 1. KPIs Généraux (Single query for performance)
        $sqlKpi = "
            SELECT 
                COUNT(*) as total_fleet,
                COALESCE(SUM(purchase_price), 0) as total_value,
                SUM(CASE WHEN EXISTS (
                    SELECT 1 FROM contract c 
                    LEFT JOIN contract_vehicle_demand_vehicle ad ON vehicle.id = ad.vehicle_id
                    LEFT JOIN contract_vehicle_demand vd ON ad.contract_vehicle_demand_id = vd.id
                    WHERE (c.vehicle_id = vehicle.id OR c.id = vd.contract_id)
                    AND c.status IN ('ACTIVE', 'EN COURS', 'EN_COURS', 'VALIDÉ', 'Actif', 'En cours', 'Validé')
                ) THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN EXISTS (SELECT 1 FROM maintenance m WHERE m.vehicle_id = vehicle.id AND m.status IN ('En cours', 'In Progress', 'EN_COURS') AND m.deleted_at IS NULL) THEN 1 ELSE 0 END) as maintenance_count,
                (SELECT COALESCE(SUM(cost), 0) FROM maintenance m2 WHERE m2.status IN ('En cours', 'In Progress', 'EN_COURS') AND m2.deleted_at IS NULL) as active_maintenance_cost,
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

        // 2. Distribution par Statut - Comptage indépendant (un véhicule peut être dans plusieurs catégories)
        // "En Location-Vente" = a un contrat ACTIF valide (pas terminé, suspendu, annulé)
        // "En Maintenance" = a au moins une maintenance dont le statut est 'En cours'
        // "Disponible" = ni contrat actif, ni maintenance en cours

        $activeContractStatuses = ['ACTIVE', 'EN COURS', 'EN_COURS', 'VALIDÉ', 'VALIDATED', 'Actif', 'En cours', 'Validé', 'NEW', 'PENDING', 'BROUILLON', 'EN ATTENTE', 'NOUVEAU', 'Demande'];
        $activeContractPlaceholders = implode(',', array_fill(0, count($activeContractStatuses), '?'));

        $soldContractStatuses = ['TERMINÉ', 'SOLDÉ', 'Solder', 'Vendu'];
        $soldContractPlaceholders = implode(',', array_fill(0, count($soldContractStatuses), '?'));

        // PRIORITIZED EXCLUSIVE CATEGORIZATION

        // 1. SOLD (Highest priority)
        $sqlSold = "
            SELECT COUNT(DISTINCT v.id)
            FROM vehicle v
            WHERE v.deleted_at IS NULL
            AND (
                v.statut = 'Vendu'
                OR EXISTS (
                    SELECT 1 FROM contract c 
                    WHERE c.vehicle_id = v.id 
                    AND c.deleted_at IS NULL
                    AND c.status IN ($soldContractPlaceholders)
                )
            )
        ";
        $soldCount = (int)$conn->fetchOne($sqlSold, array_merge($soldContractStatuses));

        // 2. RENTING (If not Sold)
        $sqlRenting = "
            SELECT COUNT(DISTINCT v.id)
            FROM vehicle v
            WHERE v.deleted_at IS NULL
            AND (
                -- Contrat direct sur le véhicule
                EXISTS (
                    SELECT 1 FROM contract c 
                    WHERE c.vehicle_id = v.id 
                    AND c.deleted_at IS NULL
                    AND c.status IN ($activeContractPlaceholders)
                )
                OR
                -- Contrat de flotte contenant le véhicule
                EXISTS (
                    SELECT 1 FROM contract_vehicle_demand_vehicle ad
                    INNER JOIN contract_vehicle_demand vd ON ad.contract_vehicle_demand_id = vd.id
                    INNER JOIN contract c ON vd.contract_id = c.id
                    WHERE ad.vehicle_id = v.id
                    AND c.deleted_at IS NULL
                    AND c.status IN ($activeContractPlaceholders)
                )
                OR v.statut = 'En Location-Vente'
            )
            -- Exclusion des véhicules déjà comptés comme \"Vendus\"
            AND v.statut != 'Vendu'
            AND NOT EXISTS (
                SELECT 1 FROM contract c2 
                WHERE c2.vehicle_id = v.id 
                AND c2.deleted_at IS NULL 
                AND c2.status IN ($soldContractPlaceholders)
            )
        ";
        $rentingCount = (int)$conn->fetchOne($sqlRenting, array_merge($activeContractStatuses, $activeContractStatuses, $soldContractStatuses));

        // 3. MAINTENANCE (If not Sold and not Rented)
        $sqlMaintenance = "
            SELECT COUNT(DISTINCT v.id)
            FROM vehicle v
            INNER JOIN maintenance m ON m.vehicle_id = v.id
            WHERE v.deleted_at IS NULL
            AND m.deleted_at IS NULL
            AND m.status IN ('En cours', 'In Progress', 'EN_COURS')
            -- Exclusions
            AND v.statut NOT IN ('Vendu', 'En Location-Vente')
            AND NOT EXISTS (SELECT 1 FROM contract c WHERE c.vehicle_id = v.id AND c.deleted_at IS NULL AND c.status IN ($soldContractPlaceholders))
            AND NOT EXISTS (SELECT 1 FROM contract c WHERE c.vehicle_id = v.id AND c.deleted_at IS NULL AND c.status IN ($activeContractPlaceholders))
        ";
        $maintenanceCount = (int)$conn->fetchOne($sqlMaintenance, array_merge($soldContractStatuses, $activeContractStatuses));

        // 4. AVAILABLE (Everything else)
        $sqlExclusiveAvailable = "
            SELECT COUNT(DISTINCT v.id)
            FROM vehicle v
            WHERE v.deleted_at IS NULL
            -- Exclusions
            AND v.statut NOT IN ('Vendu', 'En Location-Vente')
            AND NOT EXISTS (SELECT 1 FROM contract c WHERE c.vehicle_id = v.id AND c.deleted_at IS NULL AND c.status IN ($soldContractPlaceholders))
            AND NOT EXISTS (SELECT 1 FROM contract c WHERE c.vehicle_id = v.id AND c.deleted_at IS NULL AND c.status IN ($activeContractPlaceholders))
            AND NOT EXISTS (SELECT 1 FROM maintenance m WHERE m.vehicle_id = v.id AND m.deleted_at IS NULL AND m.status IN ('En cours', 'In Progress', 'EN_COURS'))
        ";
        $availableCount = (int)$conn->fetchOne($sqlExclusiveAvailable, array_merge($soldContractStatuses, $activeContractStatuses));

        $distribution = [
            ['statut' => 'En Location-Vente', 'count' => $rentingCount],
            ['statut' => 'Disponible', 'count' => $availableCount],
            ['statut' => 'En Maintenance', 'count' => $maintenanceCount],
            ['statut' => 'Vendu', 'count' => $soldCount]
        ];

        // 3. Trends (Real Maintenance Costs)
        $groupByYear = $months > 36;
        $dateFormat = $groupByYear ? '%Y' : '%Y-%m';

        // Fetch custom budgets from DB
        $budgetMap = [];
        try {
            $sqlBudgets = "SELECT period, amount FROM maintenance_budget WHERE deleted_at IS NULL";
            $rawBudgets = $conn->fetchAllAssociative($sqlBudgets);
            foreach ($rawBudgets as $rb) {
                $budgetMap[$rb['period']] = (float)$rb['amount'];
            }
        }
        catch (\Exception $e) { /* Table might not exist yet */
        }

        $sqlTrendsMaint = "
            SELECT 
                DATE_FORMAT(date_intervention, '$dateFormat') as period,
                SUM(cost) as total_cost
            FROM maintenance
            WHERE date_intervention >= DATE_SUB(CURRENT_DATE(), INTERVAL :months MONTH)
            AND deleted_at IS NULL
            GROUP BY DATE_FORMAT(date_intervention, '$dateFormat')
            ORDER BY period ASC
        ";
        $maintDataRaw = $conn->executeQuery($sqlTrendsMaint, ['months' => $months])->fetchAllAssociative();

        // Map raw data for easy access
        $maintMap = [];
        foreach ($maintDataRaw as $row) {
            $maintMap[$row['period']] = (float)$row['total_cost'];
        }

        // Generate full period list for the chart
        $maintenanceTrends = [];
        $budgetTrends = []; // Still zero as no budget entity is linked

        $startDate = new \DateTime();
        $startDate->modify("-" . ($months - 1) . " months");
        if ($groupByYear) {
            $startDate->modify("first day of january this year");
        }
        else {
            $startDate->modify("first day of this month");
        }

        $endDate = new \DateTime();
        $endDate->modify("first day of this month");

        $current = clone $startDate;
        $maxIter = 200;
        $iter = 0;

        $endKey = $endDate->format($groupByYear ? 'Y' : 'Y-m');

        while ($iter < $maxIter) {
            $iter++;
            $periodKey = $current->format($groupByYear ? 'Y' : 'Y-m');

            $maintenanceTrends[] = [
                'period' => $periodKey,
                'month' => $current->format('n'),
                'year' => $current->format('Y'),
                'cost' => $maintMap[$periodKey] ?? 0
            ];

            $periodicBudget = 0;
            if ($groupByYear) {
                for ($m = 1; $m <= 12; $m++) {
                    $mKey = $periodKey . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
                    $periodicBudget += $budgetMap[$mKey] ?? 200000;
                }
            }
            else {
                $periodicBudget = $budgetMap[$periodKey] ?? 200000;
            }

            $budgetTrends[] = [
                'period' => $periodKey,
                'month' => $current->format('n'),
                'year' => $current->format('Y'),
                'amount' => $periodicBudget
            ];

            if ($periodKey === $endKey)
                break;

            if ($groupByYear) {
                $current->modify("+1 year");
            }
            else {
                $current->modify("+1 month");
            }
        }

        $trends = [
            'maintenance' => $maintenanceTrends,
            'budget' => $budgetTrends
        ];

        // 4. Alertes Véhicules (100% Dynamic from DB triggers)
        $sqlAlerts = "
            (
                -- ALERTE MAINTENANCE (Vidange)
                SELECT 
                    v.id, v.uuid, v.immatriculation, v.marque, v.modele, 
                    COALESCE(cl.last_name, cl2.last_name, c.last_name) as client_last_name,
                    COALESCE(cl.first_name, cl2.first_name, c.first_name) as client_first_name,
                    CASE 
                        WHEN v.kilometrage >= v.prochain_entretien THEN CONCAT('Vidange dépassée de ', (v.kilometrage - v.prochain_entretien), ' km')
                        ELSE 'Entretien prévu bientôt'
                    END as problem,
                    CASE 
                        WHEN v.kilometrage >= v.prochain_entretien THEN 'Critique'
                        ELSE 'Attention'
                    END as niveau,
                    0 as cost
                FROM vehicle v
                LEFT JOIN client c ON v.client_id = c.id
                -- Join contracts to get client if not direct
                LEFT JOIN contract ctr ON v.id = ctr.vehicle_id AND ctr.status IN ('ACTIVE', 'EN COURS', 'EN_COURS', 'VALIDÉ', 'Actif', 'En cours', 'Validé')
                LEFT JOIN client cl ON ctr.client_id = cl.id
                -- Join fleet assignment
                LEFT JOIN contract_vehicle_demand_vehicle ad ON v.id = ad.vehicle_id
                LEFT JOIN contract_vehicle_demand vd ON ad.contract_vehicle_demand_id = vd.id
                LEFT JOIN contract ctr2 ON vd.contract_id = ctr2.id AND ctr2.status IN ('ACTIVE', 'EN COURS', 'EN_COURS', 'VALIDÉ', 'Actif', 'En cours', 'Validé')
                LEFT JOIN client cl2 ON ctr2.client_id = cl2.id
                WHERE (v.maintenance_alert = 1 OR v.kilometrage >= v.prochain_entretien)
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
                -- ALERTE COMPLIANCE (Assurance, CT, etc.) - Only the latest document per type
                SELECT 
                    v.id, v.uuid, v.immatriculation, v.marque, v.modele, 
                    COALESCE(cl.last_name, cl2.last_name, c.last_name) as client_last_name,
                    COALESCE(cl.first_name, cl2.first_name, c.first_name) as client_first_name,
                    CONCAT(doc.type, 
                        CASE 
                            WHEN doc.end_date < CURRENT_DATE() THEN ' expiré'
                            ELSE CONCAT(' expire dans ', DATEDIFF(doc.end_date, CURRENT_DATE()), ' jours')
                        END
                    ) as problem,
                    CASE 
                        WHEN doc.end_date < CURRENT_DATE() THEN 'Critique'
                        ELSE 'Attention'
                    END as niveau,
                    doc.renewal_cost as cost
                FROM vehicle v
                LEFT JOIN client c ON v.client_id = c.id
                -- Join contracts to get client if not direct
                LEFT JOIN contract ctr ON v.id = ctr.vehicle_id AND ctr.status IN ('ACTIVE', 'EN COURS', 'EN_COURS', 'VALIDÉ', 'Actif', 'En cours', 'Validé')
                LEFT JOIN client cl ON ctr.client_id = cl.id
                -- Join fleet assignment
                LEFT JOIN contract_vehicle_demand_vehicle ad ON v.id = ad.vehicle_id
                LEFT JOIN contract_vehicle_demand vd ON ad.contract_vehicle_demand_id = vd.id
                LEFT JOIN contract ctr2 ON vd.contract_id = ctr2.id AND ctr2.status IN ('ACTIVE', 'EN COURS', 'EN_COURS', 'VALIDÉ', 'Actif', 'En cours', 'Validé')
                LEFT JOIN client cl2 ON ctr2.client_id = cl2.id
                JOIN vehicle_compliance_document doc ON doc.vehicle_id = v.id
                -- Filter to take ONLY the most recent doc per type
                INNER JOIN (
                    SELECT vehicle_id, type, MAX(end_date) as latest_date
                    FROM vehicle_compliance_document
                    WHERE deleted_at IS NULL
                    GROUP BY vehicle_id, type
                ) latest_doc_filter ON doc.vehicle_id = latest_doc_filter.vehicle_id 
                    AND doc.type = latest_doc_filter.type 
                    AND doc.end_date = latest_doc_filter.latest_date
                WHERE doc.end_date <= DATE_ADD(CURRENT_DATE(), INTERVAL 30 DAY)
                AND doc.deleted_at IS NULL
                AND v.deleted_at IS NULL
            )
            UNION ALL
            (
                -- ALERTE MAINTENANCE PROLONGÉE (> 3 jours en cours)
                SELECT 
                    v.id, v.uuid, v.immatriculation, v.marque, v.modele, 
                    COALESCE(cl.last_name, cl2.last_name, c.last_name) as client_last_name,
                    COALESCE(cl.first_name, cl2.first_name, c.first_name) as client_first_name,
                    CONCAT('Maintenance immobilisée depuis ', DATEDIFF(CURRENT_DATE(), m.start_date), ' jours') as problem,
                    'Critique' as niveau,
                    m.cost as cost
                FROM vehicle v
                JOIN maintenance m ON m.vehicle_id = v.id
                LEFT JOIN client c ON v.client_id = c.id
                LEFT JOIN contract ctr ON v.id = ctr.vehicle_id AND ctr.status IN ('ACTIVE', 'EN COURS', 'EN_COURS', 'VALIDÉ', 'Actif', 'En cours', 'Validé')
                LEFT JOIN client cl ON ctr.client_id = cl.id
                LEFT JOIN contract_vehicle_demand_vehicle ad ON v.id = ad.vehicle_id
                LEFT JOIN contract_vehicle_demand vd ON ad.contract_vehicle_demand_id = vd.id
                LEFT JOIN contract ctr2 ON vd.contract_id = ctr2.id AND ctr2.status IN ('ACTIVE', 'EN COURS', 'EN_COURS', 'VALIDÉ', 'Actif', 'En cours', 'Validé')
                LEFT JOIN client cl2 ON ctr2.client_id = cl2.id
                WHERE m.status IN ('EN COURS', 'En cours', 'EN_PROGRESS')
                AND m.start_date IS NOT NULL
                AND DATEDIFF(CURRENT_DATE(), m.start_date) > 3
                AND m.deleted_at IS NULL
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
        $totalExpectedDocs = $kpis['total_fleet'] * 2;
        try {
            $validDocs = (int)$conn->fetchOne($sqlComp);
            $complianceRate = $kpis['total_fleet'] > 0 ? ($validDocs / $totalExpectedDocs) * 100 : 0; // Avg 2 docs per vehicle
        }
        catch (\Exception $e) {
            $validDocs = 0;
            $complianceRate = 0;
        }

        return [
            'kpis' => array_merge($kpis, [
                'budgetMonthly' => $periodicBudget
            ]),
            'distribution' => $distribution,
            'trends' => $trends,
            'alerts' => $alerts,
            'complianceRate' => round($complianceRate, 2),
            'validDocs' => $validDocs,
            'totalExpectedDocs' => $totalExpectedDocs
        ];
    }
}