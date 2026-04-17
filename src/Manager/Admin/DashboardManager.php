<?php

namespace App\Manager\Admin;

use App\Repository\Client\ClientRepository;
use App\Repository\Client\ContractRepository;
use App\Repository\Client\MaintenanceAlertRepository;
use App\Repository\Client\PaymentRepository;
use App\Repository\Client\VehicleRepository;
use App\Repository\Admin\AuditLogRepository;
use Doctrine\ORM\EntityManagerInterface;

class DashboardManager
{
    private $clientRepository;
    private $contractRepository;
    private $maintenanceAlertRepository;
    private $paymentRepository;
    private $vehicleRepository;
    private $auditLogRepository;
    private $em;

    public function __construct(
        ClientRepository $clientRepository,
        ContractRepository $contractRepository,
        MaintenanceAlertRepository $maintenanceAlertRepository,
        PaymentRepository $paymentRepository,
        VehicleRepository $vehicleRepository,
        AuditLogRepository $auditLogRepository,
        EntityManagerInterface $em
        )
    {
        $this->clientRepository = $clientRepository;
        $this->contractRepository = $contractRepository;
        $this->maintenanceAlertRepository = $maintenanceAlertRepository;
        $this->paymentRepository = $paymentRepository;
        $this->vehicleRepository = $vehicleRepository;
        $this->auditLogRepository = $auditLogRepository;
        $this->em = $em;
    }

    public function getMainDashboardData(int $months): array
    {
        $clientMetrics = $this->clientRepository->getDashboardMetrics($months);
        $vehicleMetrics = $this->vehicleRepository->getDashboardMetrics($months);
        $contractMetrics = $this->contractRepository->getDashboardMetrics($months);
        $paymentMetrics = $this->paymentRepository->getDashboardMetrics($months);

        // Calcule le revenu mensuel (somme des paiements) pour le mois courant
        $revenueConn = $this->em->getConnection();
        $sqlCurrentMonth = "
            SELECT SUM(p.amount) as total
            FROM payment p
            WHERE p.deleted_at IS NULL
              AND UPPER(p.status) IN ('VALIDÉ', 'VALIDATED', 'ACTIVE', 'PAYÉ', 'PAID')
              AND YEAR(p.date) = YEAR(CURRENT_DATE())
              AND MONTH(p.date) = MONTH(CURRENT_DATE())
        ";
        $currentMonthRevenue = (float)$revenueConn->fetchOne($sqlCurrentMonth);

        // Calcule le revenu mensuel pour le mois précédent
        $sqlPreviousMonth = "
            SELECT SUM(p.amount) as total
            FROM payment p
            WHERE p.deleted_at IS NULL
              AND UPPER(p.status) IN ('VALIDÉ', 'VALIDATED', 'ACTIVE', 'PAYÉ', 'PAID')
              AND YEAR(p.date) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
              AND MONTH(p.date) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
        ";
        $previousMonthRevenue = (float)$revenueConn->fetchOne($sqlPreviousMonth);

        // Calcule la croissance (%)
        if ($previousMonthRevenue > 0) {
            $monthlyRevenueGrowth = (($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100;
        }
        else {
            $monthlyRevenueGrowth = $currentMonthRevenue > 0 ? 100 : 0;
        }

        // Correct key mapping from repository responses
        $totalFleet = (int)($vehicleMetrics['kpis']['total_fleet'] ?? 0);
        $stats = [
            'activeClients' => $clientMetrics['kpis']['activeClients'] ?? 0,
            'activeClientsGrowth' => 5.2,
            'totalVehicles' => $totalFleet,
            'availableVehicles' => $vehicleMetrics['distribution'][1]['count'] ?? 0,
            'monthlyRevenue' => $currentMonthRevenue,
            'monthlyRevenueGrowth' => round($monthlyRevenueGrowth, 1),
            'pendingPaymentsCount' => $paymentMetrics['kpis']['overdueCount'] ?? 0,
            'pendingPaymentsAmount' => $paymentMetrics['kpis']['totalOverdue'] ?? 0,
            'portfolioValue' => $clientMetrics['kpis']['portfolioValue'] ?? 0,
            'portfolioValueGrowth' => 8.4,
            'collectionRate' => $paymentMetrics['kpis']['collectionRate'] ?? 0,
            'collectionRateTarget' => 95,
            'idleRate' => 0,
            'idleRateTrend' => 'down'
        ];

        // Format recent activities from AuditLog
        $logs = $this->auditLogRepository->findBy([], ['id' => 'DESC'], 5);
        $recentActivity = [];
        foreach ($logs as $log) {
            $recentActivity[] = [
                'user' => $log->getUserFullName() ?: 'Système',
                'time' => $log->getCreatedAt()->format('H:i d/m'),
                'description' => $log->getAction() . ' : ' . $log->getModule()
            ];
        }

        // Format expiring contracts / risks
        $expiringContractsRaw = $contractMetrics['imminentRisks'] ?? [];
        $expiringContracts = array_map(function ($c) {
            return [
            'client' => $c['client'] ?? 'Inconnu',
            'vehicle' => $c['vehicle_info'] ?? 'Véhicule Engagé',
            'expiryDate' => 'Retard (' . (int)($c['value'] ?? 0) . ' FCFA)'
            ];
        }, array_slice($expiringContractsRaw, 0, 5));

        $urgentActionsRaw = $contractMetrics['imminentRisks'] ?? [];
        $urgentActions = array_map(function ($a) {
            return [
            'client' => $a['client'] ?? 'Inconnu',
            'delay' => 15,
            'vehicle' => 'Consulter Contrat',
            'amount' => $a['value'] ?? 0,
            'action' => 'Relance',
            ];
        }, array_slice($urgentActionsRaw, 0, 5));

        // Conformité : documents proches de l'expiration (30 jours)
        $complianceDeadline = (new \DateTime())->modify('+30 days')->format('Y-m-d');
        $today = (new \DateTime())->format('Y-m-d');
        $complianceConn = $this->em->getConnection();
        $sqlCompliance = "
            SELECT immatriculation, modele, expiry_date, type_label FROM (
                SELECT v.immatriculation, v.modele,
                    vc.assurance_expiry_date AS expiry_date, 'Assurance' AS type_label
                FROM vehicle_compliance vc
                JOIN vehicle v ON vc.vehicle_id = v.id
                WHERE vc.deleted_at IS NULL AND v.deleted_at IS NULL
                  AND vc.assurance_expiry_date IS NOT NULL
                  AND vc.assurance_expiry_date <= :deadline
                UNION ALL
                SELECT v.immatriculation, v.modele,
                    vc.technical_inspection_expiry_date, 'Visite Technique'
                FROM vehicle_compliance vc
                JOIN vehicle v ON vc.vehicle_id = v.id
                WHERE vc.deleted_at IS NULL AND v.deleted_at IS NULL
                  AND vc.technical_inspection_expiry_date IS NOT NULL
                  AND vc.technical_inspection_expiry_date <= :deadline
                UNION ALL
                SELECT v.immatriculation, v.modele,
                    vc.road_tax_expiry_date, 'Vignette'
                FROM vehicle_compliance vc
                JOIN vehicle v ON vc.vehicle_id = v.id
                WHERE vc.deleted_at IS NULL AND v.deleted_at IS NULL
                  AND vc.road_tax_expiry_date IS NOT NULL
                  AND vc.road_tax_expiry_date <= :deadline
                UNION ALL
                SELECT v.immatriculation, v.modele,
                    vc.transport_license_expiry_date, 'Licence Transport'
                FROM vehicle_compliance vc
                JOIN vehicle v ON vc.vehicle_id = v.id
                WHERE vc.deleted_at IS NULL AND v.deleted_at IS NULL
                  AND vc.transport_license_expiry_date IS NOT NULL
                  AND vc.transport_license_expiry_date <= :deadline
                UNION ALL
                SELECT v.immatriculation, v.modele,
                    vc.fire_extinguisher_expiry_date, 'Extincteur'
                FROM vehicle_compliance vc
                JOIN vehicle v ON vc.vehicle_id = v.id
                WHERE vc.deleted_at IS NULL AND v.deleted_at IS NULL
                  AND vc.fire_extinguisher_expiry_date IS NOT NULL
                  AND vc.fire_extinguisher_expiry_date <= :deadline
                UNION ALL
                SELECT v.immatriculation, v.modele,
                    vc.carte_grise_expiry_date, 'Carte Grise'
                FROM vehicle_compliance vc
                JOIN vehicle v ON vc.vehicle_id = v.id
                WHERE vc.deleted_at IS NULL AND v.deleted_at IS NULL
                  AND vc.carte_grise_expiry_date IS NOT NULL
                  AND vc.carte_grise_expiry_date <= :deadline
            ) AS t
            ORDER BY expiry_date ASC
            LIMIT 8
        ";
        $complianceRaw = $complianceConn->fetchAllAssociative($sqlCompliance, ['deadline' => $complianceDeadline]);
        $maintenanceAlerts = [];
        foreach ($complianceRaw as $row) {
            $expiry = new \DateTime($row['expiry_date']);
            $now = new \DateTime($today);
            $diff = (int)$now->diff($expiry)->days * ($expiry >= $now ? 1 : -1);
            if ($diff < 0) {
                $label = 'Expiré depuis ' . abs($diff) . 'j';
                $severity = 'danger';
            }
            elseif ($diff === 0) {
                $label = "Expire aujourd'hui";
                $severity = 'danger';
            }
            elseif ($diff === 1) {
                $label = 'Expire demain';
                $severity = 'danger';
            }
            elseif ($diff <= 7) {
                $label = 'Expire dans ' . $diff . ' jours';
                $severity = 'danger';
            }
            else {
                $label = 'Dans ' . $diff . ' jours';
                $severity = 'warning';
            }
            $maintenanceAlerts[] = [
                'vehicle' => ($row['modele'] ?? '') . ' - ' . $row['immatriculation'],
                'type' => $row['type_label'],
                'dueDate' => $label,
                'severity' => $severity
            ];
        }

        // Ventes mensuelles : contrats avec statut terminé (véhicule considéré comme vendu)
        $groupByYear = $months > 36;
        $datePart = $groupByYear ? '%Y' : '%Y-%m';
        $startDate = (new \DateTime())->modify("-{$months} months")->format('Y-m-01');

        $conn = $this->em->getConnection();

        $terminalStatuses = ['TERMINÉ', 'Terminé', 'SOLDÉ', 'Soldé', 'SOLD', 'Completed', 'COMPLETED', 'VENDU', 'Vendu'];
        $termPlaceholders = implode(',', array_fill(0, count($terminalStatuses), '?'));

        $sqlSales = "
            SELECT DATE_FORMAT(c.updated_at, '{$datePart}') as period, COUNT(DISTINCT c.id) as sales
            FROM contract c
            WHERE c.deleted_at IS NULL
              AND c.updated_at >= ?
              AND c.status IN ($termPlaceholders)
            GROUP BY DATE_FORMAT(c.updated_at, '{$datePart}')
            ORDER BY period ASC
        ";
        $salesRaw = $conn->fetchAllAssociative($sqlSales, array_merge([$startDate], $terminalStatuses));
        $salesByPeriod = [];
        foreach ($salesRaw as $row) {
            $salesByPeriod[$row['period']] = (int)$row['sales'];
        }
        $monthlySalesData = [];
        foreach ($salesByPeriod as $period => $sales) {
            $monthlySalesData[] = ['month' => $period, 'sales' => $sales];
        }

        // Dossiers en attente de validation : contrats récents non encore validés
        $pendingStatuses = ['NEW', 'PENDING', 'En Attente', 'en attente', 'DRAFT', 'BROUILLON'];
        $pendingPlaceholders = implode(',', array_fill(0, count($pendingStatuses), '?'));
        $pendingConn = $this->em->getConnection();
        $sqlPending = "
            SELECT
                CONCAT(COALESCE(cl.first_name, ''), ' ', COALESCE(cl.last_name, COALESCE(cl.name, ''))) AS client_name,
                cl.phone,
                COALESCE(cl.type, c.usage_type, 'Particulier') AS profil,
                c.created_at AS date_demande,
                c.status
            FROM contract c
            JOIN client cl ON c.client_id = cl.id
            WHERE c.deleted_at IS NULL
              AND cl.deleted_at IS NULL
              AND (c.status IN ($pendingPlaceholders)
                   OR UPPER(c.status) IN ('NEW', 'PENDING', 'EN ATTENTE', 'DRAFT'))
            ORDER BY c.created_at DESC
            LIMIT 8
        ";
        $pendingRaw = $pendingConn->fetchAllAssociative($sqlPending, $pendingStatuses);
        $recentOnboardings = array_map(function ($row) {
            $statusRaw = strtoupper($row['status'] ?? '');
            if (in_array($statusRaw, ['VALIDÉ', 'VALIDATED', 'ACTIVE', 'VALIDÉ', 'ACTIVE'])) {
                $status = 'approved';
            }
            elseif (in_array($statusRaw, ['REJETÉ', 'REJECTED', 'ANNULÉ', 'CANCELLED'])) {
                $status = 'rejected';
            }
            else {
                $status = 'pending';
            }
            return [
            'name' => trim($row['client_name']) ?: 'Inconnu',
            'phone' => $row['phone'] ?? '--',
            'type' => $row['profil'] ?? 'Particulier',
            'date' => $row['date_demande'] ?? null,
            'status' => $status
            ];
        }, $pendingRaw);

        $data = [
            'stats' => $stats,
            'totalVehiclesFleet' => $totalFleet,
            'recentOnboardings' => $recentOnboardings,
            'maintenanceAlerts' => $maintenanceAlerts,
            'expiringContracts' => $expiringContracts,
            'recentActivity' => $recentActivity,
            'riskDistribution' => [
                ['label' => 'En Location', 'value' => $vehicleMetrics['distribution'][0]['count'] ?? 0],
                ['label' => 'Au Parking (Dispo)', 'value' => $vehicleMetrics['distribution'][1]['count'] ?? 0],
                ['label' => 'En Panne/Maintenance', 'value' => $vehicleMetrics['distribution'][2]['count'] ?? 0],
                ['label' => 'Vendu', 'value' => $vehicleMetrics['distribution'][3]['count'] ?? 0]
            ],
            'urgentActions' => $urgentActions,
            'monthlySalesData' => $monthlySalesData,
            'revenueChartData' => [] // We will override this right below
        ];

        // --- EVOLUTION DES ENCAISSEMENTS (Cashflow validé par mois/année) ---
        $cashflowConn = $this->em->getConnection();
        $sqlCashflow = "
            SELECT 
                DATE_FORMAT(p.date, '{$datePart}') as period, 
                SUM(p.amount) as total
            FROM payment p
            WHERE p.deleted_at IS NULL
              AND p.date >= ?
              AND UPPER(p.status) IN ('VALIDÉ', 'VALIDATED', 'ACTIVE', 'PAYÉ', 'PAID')
            GROUP BY DATE_FORMAT(p.date, '{$datePart}')
            ORDER BY period ASC
        ";
        $cashflowRaw = $cashflowConn->fetchAllAssociative($sqlCashflow, [$startDate]);
        $revenueChartData = [];
        foreach ($cashflowRaw as $row) {
            $revenueChartData[] = [
                'month' => $row['period'],
                'paid' => (float)$row['total']
            ];
        }
        $data['revenueChartData'] = $revenueChartData;

        return $data;
    }

    public function getAdminDashboardData(): array
    {
        $conn = $this->em->getConnection();

        // 1. KPIs
        // Total audit actions today
        $sqlTotalAudit = "SELECT COUNT(id) FROM audit_log WHERE DATE(created_at) = CURRENT_DATE()";
        $totalAuditActions = (int)$conn->fetchOne($sqlTotalAudit);

        // Active collaborators today
        $sqlActiveCollabs = "SELECT COUNT(DISTINCT user_full_name) FROM audit_log WHERE DATE(created_at) = CURRENT_DATE()";
        $activeCollaborators = (int)$conn->fetchOne($sqlActiveCollabs);

        // Pending Approvals (Contracts NEW/PENDING + Payments En attente + Vehicles Nouveau)
        $sqlPendingContracts = "SELECT COUNT(id) FROM contract WHERE deleted_at IS NULL AND UPPER(status) IN ('NEW', 'PENDING', 'BROUILLON', 'EN ATTENTE')";
        $pendingContractsCount = (int)$conn->fetchOne($sqlPendingContracts);

        $sqlPendingPayments = "SELECT COUNT(id) FROM payment WHERE deleted_at IS NULL AND UPPER(status) IN ('EN ATTENTE', 'PENDING')";
        $pendingPaymentsCount = (int)$conn->fetchOne($sqlPendingPayments);

        $sqlPendingVehicles = "SELECT COUNT(id) FROM vehicle WHERE deleted_at IS NULL AND (UPPER(statut) = 'NOUVEAU' OR UPPER(pipeline_status) = 'VALIDATION_PENDING')";
        $pendingVehiclesCount = (int)$conn->fetchOne($sqlPendingVehicles);

        $pendingApprovals = $pendingContractsCount + $pendingPaymentsCount + $pendingVehiclesCount;

        // 2. Pending Tasks List
        $pendingTasks = [];

        // Feed from Contracts
        $sqlRawContracts = "
            SELECT c.uuid, c.reference, c.created_at, 
                   CONCAT(COALESCE(cl.first_name, ''), ' ', COALESCE(cl.last_name, '')) as requester
            FROM contract c
            JOIN client cl ON c.client_id = cl.id
            WHERE c.deleted_at IS NULL AND UPPER(c.status) IN ('NEW', 'PENDING', 'BROUILLON', 'EN ATTENTE')
            ORDER BY c.created_at DESC LIMIT 5
        ";
        $resContracts = $conn->fetchAllAssociative($sqlRawContracts);
        foreach ($resContracts as $row) {
            $pendingTasks[] = [
                'id' => $row['uuid'],
                'type' => 'Validation Contrat',
                'description' => 'Validation requise pour le contrat ' . $row['reference'],
                'requester' => trim($row['requester']) ?: 'Inconnu',
                'date' => $row['created_at'],
                'priority' => 'high'
            ];
        }

        // Feed from Payments
        $sqlRawPayments = "
            SELECT p.uuid, p.reference, p.amount, p.date, p.recorded_by
            FROM payment p
            WHERE p.deleted_at IS NULL AND UPPER(p.status) IN ('EN ATTENTE', 'PENDING')
            ORDER BY p.date DESC LIMIT 5
        ";
        $resPayments = $conn->fetchAllAssociative($sqlRawPayments);
        foreach ($resPayments as $row) {
            $pendingTasks[] = [
                'id' => $row['uuid'],
                'type' => 'Validation Paiement',
                'description' => 'Validation du paiement ' . $row['reference'] . ' (' . number_format($row['amount'], 0, ',', ' ') . ' FCFA)',
                'requester' => $row['recorded_by'] ?: 'Système',
                'date' => $row['date'],
                'priority' => 'medium'
            ];
        }

        // 3. Audit Logs
        $auditLogsRaw = $this->auditLogRepository->findBy([], ['id' => 'DESC'], 10);
        $auditLogs = [];
        foreach ($auditLogsRaw as $log) {
            $auditLogs[] = [
                'user' => $log->getUserFullName() ?: 'Système',
                'avatar' => $log->getUserInitials() ?: 'SY',
                'module' => $log->getModule(),
                'action' => $log->getAction(),
                'details' => $log->getDetails(),
                'time' => $log->getCreatedAt()->format('Y-m-d H:i:s'),
                'status' => str_contains(strtolower($log->getDetails() ?? ''), 'erreur') ? 'Alerte' : 'Succès'
            ];
        }

        // 4. Charts - Distribution des types d'actions
        $sqlActionDist = "SELECT action, COUNT(id) as count FROM audit_log GROUP BY action";
        $resActionDist = $conn->fetchAllAssociative($sqlActionDist);
        $actionLabels = [];
        $actionSeries = [];
        foreach ($resActionDist as $row) {
            $actionLabels[] = $row['action'];
            $actionSeries[] = (int)$row['count'];
        }

        // Collaborator Activity - Top 6
        $sqlCollabActivity = "
            SELECT user_full_name, 
                   COUNT(id) as total_actions,
                   SUM(CASE WHEN action = 'Validation' OR action LIKE 'Validation%' THEN 1 ELSE 0 END) as processed
            FROM audit_log 
            WHERE user_full_name IS NOT NULL
            GROUP BY user_full_name
            ORDER BY total_actions DESC
            LIMIT 6
        ";
        $resCollabActivity = $conn->fetchAllAssociative($sqlCollabActivity);
        $collabNames = [];
        $collabTotalArr = [];
        $collabProcessedArr = [];
        $collabRequestedArr = [];

        $sqlCollabReq = "
            SELECT user_full_name, COUNT(id) as requested
            FROM audit_log
            WHERE action IN ('Création', 'Encaissement')
            GROUP BY user_full_name
        ";
        $reqMapArr = [];
        foreach ($conn->fetchAllAssociative($sqlCollabReq) as $r) {
            $reqMapArr[$r['user_full_name']] = (int)$r['requested'];
        }

        foreach ($resCollabActivity as $row) {
            $collabNames[] = $row['user_full_name'];
            $collabTotalArr[] = (int)$row['total_actions'];
            $collabProcessedArr[] = (int)$row['processed'];
            $collabRequestedArr[] = $reqMapArr[$row['user_full_name']] ?? 0;
        }

        $result = [
            'stats' => [
                'totalAuditActions' => $totalAuditActions,
                'pendingApprovals' => $pendingApprovals,
                'criticalAnomalies' => 0,
                'activeCollaborators' => $activeCollaborators
            ],
            'pendingTasks' => $pendingTasks,
            'auditLogs' => $auditLogs,
            'auditActionChart' => [
                'series' => $actionSeries,
                'labels' => $actionLabels
            ],
            'approvalTrendChart' => [
                'requested' => $collabRequestedArr,
                'processed' => $collabProcessedArr,
                'totalActions' => $collabTotalArr,
                'collaborators' => $collabNames
            ]
        ];

        return $this->ensureUtf8Recursive($result);
    }

    /**
     * Helper to ensure all strings in an array are valid UTF-8
     */
    private function ensureUtf8Recursive(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->ensureUtf8Recursive($value);
            }
            elseif (is_string($value)) {
                // Try to detect and convert to UTF-8 if it looks malformed
                if (!mb_check_encoding($value, 'UTF-8')) {
                    $data[$key] = mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
                }
            }
        }
        return $data;
    }
}