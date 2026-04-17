<?php

namespace App\Repository\Client;

use App\Entity\Client\Contract;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contract>
 *
 * @method Contract|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contract|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contract[]    findAll()
 * @method Contract[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContractRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contract::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Contract $entity, bool $flush = true): void
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
    public function remove(Contract $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.client', 'cl')
            ->leftJoin('c.vehicle', 'v')
            ->addSelect('cl')
            ->addSelect('v');

        if (!empty($filters['search'])) {
            $search = '%' . mb_strtolower($filters['search']) . '%';
            $qb->andWhere('LOWER(c.reference) LIKE :search OR LOWER(cl.firstName) LIKE :search OR LOWER(cl.lastName) LIKE :search OR LOWER(v.marque) LIKE :search OR LOWER(v.modele) LIKE :search OR LOWER(v.immatriculation) LIKE :search')
                ->setParameter('search', $search);
        }

        if (!empty($filters['status'])) {
            $status = $filters['status'];
            if ($status === 'VALIDÉ') {
                $qb->andWhere('c.status IN (:statuses) OR LOWER(c.status) IN (:lStatuses)')
                    ->setParameter('statuses', ['VALIDÉ', 'ACTIVE', 'EN COURS', 'VALIDATED'])
                    ->setParameter('lStatuses', ['validé', 'active', 'en cours', 'validated']);
            }
            elseif ($status === 'NEW') {
                $qb->andWhere('c.status IN (:statuses) OR LOWER(c.status) IN (:lStatuses)')
                    ->setParameter('statuses', ['NEW', 'PENDING', 'En Attente'])
                    ->setParameter('lStatuses', ['new', 'pending', 'en attente']);
            }
            else {
                $qb->andWhere('c.status = :status OR LOWER(c.status) = :lStatus')
                    ->setParameter('status', $status)
                    ->setParameter('lStatus', mb_strtolower($status));
            }
        }

        if (!empty($filters['paymentStatus'])) {
            $qb->andWhere('c.paymentStatus = :paymentStatus')
                ->setParameter('paymentStatus', $filters['paymentStatus']);
        }

        if (!empty($filters['startDateMin'])) {
            $qb->andWhere('c.startDate >= :startDateMin')
                ->setParameter('startDateMin', new \DateTime($filters['startDateMin']));
        }

        if (!empty($filters['startDateMax'])) {
            $qb->andWhere('c.startDate <= :startDateMax')
                ->setParameter('startDateMax', new \DateTime($filters['startDateMax']));
        }

        if (isset($filters['progressMin']) || isset($filters['progressMax'])) {
            if (isset($filters['progressMin'])) {
                $subMin = "SELECT SUM(p_min.amount) FROM App\Entity\Client\Payment p_min " .
                    "WHERE p_min.contract = c AND p_min.deletedAt IS NULL " .
                    "AND (p_min.status IN ('VALIDÉ', 'VALIDATED', 'VALIDé') OR LOWER(p_min.status) IN ('validé', 'validated')) " .
                    "AND p_min.type NOT IN ('RÉPARATION_CLIENT', 'FRAIS_AGENCE', 'PÉNALITÉ')";

                $qb->andWhere("( (c.totalAmount * :progressMin / 100) + COALESCE(c.fraisDossier, 0) ) <= ($subMin)")
                    ->setParameter('progressMin', $filters['progressMin']);
            }

            if (isset($filters['progressMax'])) {
                $subMax = "SELECT SUM(p_max.amount) FROM App\Entity\Client\Payment p_max " .
                    "WHERE p_max.contract = c AND p_max.deletedAt IS NULL " .
                    "AND (p_max.status IN ('VALIDÉ', 'VALIDATED', 'VALIDé') OR LOWER(p_max.status) IN ('validé', 'validated')) " .
                    "AND p_max.type NOT IN ('RÉPARATION_CLIENT', 'FRAIS_AGENCE', 'PÉNALITÉ')";

                $qb->andWhere("( (c.totalAmount * :progressMax / 100) + COALESCE(c.fraisDossier, 0) ) >= ($subMax)")
                    ->setParameter('progressMax', $filters['progressMax']);
            }
        }

        $qb->orderBy('c.createdAt', 'DESC');

        if (!empty($filters['count']) && is_numeric($filters['count'])) {
            $qb->setMaxResults((int)$filters['count']);
        }
        else {
            $qb->setMaxResults(20);
        }

        return $qb->getQuery()->getResult();
    }

    public function findLateContracts(array $filters): array
    {
        $today = new \DateTimeImmutable('today');

        $qb = $this->createQueryBuilder('c')
            ->innerJoin('c.paymentSchedules', 'ps')
            ->leftJoin('c.client', 'cl')
            ->leftJoin('c.vehicle', 'v')
            ->leftJoin('v.brand', 'b')
            ->leftJoin('v.vehicleModel', 'vm')
            ->addSelect('cl')
            ->addSelect('v')
            ->addSelect('b')
            ->addSelect('vm')
            ->andWhere('ps.status IN (:lateStatuses)')
            ->andWhere('ps.expectedDate < :today')
            ->setParameter('lateStatuses', ['En retard', 'Partiel'])
            ->setParameter('today', $today);

        // Security: Exclude non-active or suspended contracts for recovery view
        $qb->andWhere('c.status NOT IN (:excludedStatuses)')
            ->setParameter('excludedStatuses', ['ANNULÉ', 'SOLDÉ', 'RÉSILIÉ', 'EXPIRED']);

        if (!empty($filters['search'])) {
            $search = '%' . mb_strtolower($filters['search']) . '%';
            $qb->andWhere('LOWER(c.reference) LIKE :search OR LOWER(cl.firstName) LIKE :search OR LOWER(cl.lastName) LIKE :search OR LOWER(v.immatriculation) LIKE :search')
                ->setParameter('search', $search);
        }

        // IMPORTANT: We want distinct contracts
        $qb->groupBy('c.id');
        $qb->orderBy('c.id', 'DESC');

        if (!empty($filters['count']) && is_numeric($filters['count'])) {
            $qb->setMaxResults((int)$filters['count']);
        }

        return $qb->getQuery()->getResult();
    }

    public function getDashboardMetrics(int $months = 6): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $startDate = (new \DateTime())->modify("-{$months} months")->format('Y-m-01');
        $groupByYear = $months > 36;

        // 1. KPIs
        $totalContracts = (int)$conn->fetchOne("SELECT COUNT(id) FROM contract WHERE deleted_at IS NULL");

        // Growth (Last Year)
        $lastYearDate = (new \DateTime())->modify("-1 year")->format('Y-m-d');
        $totalContractsLastYear = (int)$conn->fetchOne("
            SELECT COUNT(id) FROM contract 
            WHERE deleted_at IS NULL AND created_at <= :lastYear
        ", ['lastYear' => $lastYearDate]);
        $totalContractsGrowth = $totalContractsLastYear > 0
            ? round((($totalContracts - $totalContractsLastYear) / $totalContractsLastYear) * 100, 1)
            : 0;

        $activeStatuses = ['VALIDÉ', 'ACTIVE', 'En cours', 'VALIDATED'];
        $activeContracts = (int)$conn->fetchOne("
            SELECT COUNT(id) FROM contract 
            WHERE deleted_at IS NULL AND status IN (?)
        ", [$activeStatuses], [\Doctrine\DBAL\Connection::PARAM_STR_ARRAY]);

        $firstDayOfMonth = (new \DateTime())->format('Y-m-01');

        // Total Expected from the beginning of time until the start of this month
        $totalExpBeforeMonth = (float)$conn->fetchOne("
            SELECT SUM(amount) FROM payment_schedule 
            WHERE deleted_at IS NULL AND expected_date < :start
        ", ['start' => $firstDayOfMonth]);

        // Total Paid from the beginning of time until the start of this month
        $totalPaidBeforeMonth = (float)$conn->fetchOne("
            SELECT SUM(amount) FROM payment 
            WHERE deleted_at IS NULL AND status IN ('VALIDÉ', 'VALIDATED') AND date < :start
              AND type NOT IN ('Apport Initial', 'Frais de dossier', 'RÉPARATION_CLIENT', 'FRAIS_AGENCE', 'PÉNALITÉ')
        ", ['start' => $firstDayOfMonth]);

        $carryOverDebt = max(0, $totalExpBeforeMonth - $totalPaidBeforeMonth);

        // Expected for current month
        $currentMonthExpected = (float)$conn->fetchOne("
            SELECT SUM(amount) FROM payment_schedule 
            WHERE deleted_at IS NULL AND expected_date >= :start 
            AND expected_date <= LAST_DAY(CURRENT_DATE())
        ", ['start' => $firstDayOfMonth]);

        $mrr = $currentMonthExpected + $carryOverDebt;

        // MRR (Last month)
        $lastMonthStart = (new \DateTime())->modify("-1 month")->format('Y-m-01');
        $lastMonthEnd = (new \DateTime())->modify("-1 month")->format('Y-m-t');
        $mrrLastMonth = (float)$conn->fetchOne("
            SELECT SUM(amount) FROM payment_schedule 
            WHERE deleted_at IS NULL AND expected_date >= :start AND expected_date <= :end
        ", ['start' => $lastMonthStart, 'end' => $lastMonthEnd]);
        $mrrGrowth = $mrrLastMonth > 0 ? round((($currentMonthExpected - $mrrLastMonth) / $mrrLastMonth) * 100, 1) : 0;

        // Defect Rate (Contracts with at least one overdue schedule / active contracts)
        $lateContractsCount = (int)$conn->fetchOne("
            SELECT COUNT(DISTINCT contract_id) FROM payment_schedule
            WHERE deleted_at IS NULL AND status IN ('En retard', 'Partiel') AND expected_date < CURRENT_DATE()
        ");
        $defectRate = $activeContracts > 0 ? round(($lateContractsCount / $activeContracts) * 100, 1) : 0;

        // Tendance du Défaut (comparer avec il y a 30 jours)
        $lateContractsCount30d = (int)$conn->fetchOne("
            SELECT COUNT(DISTINCT contract_id) FROM payment_schedule
            WHERE deleted_at IS NULL AND status IN ('En retard', 'Partiel') AND expected_date < DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
        ");
        $activeContracts30d = (int)$conn->fetchOne("
            SELECT COUNT(id) FROM contract 
            WHERE deleted_at IS NULL AND status IN (?) AND created_at <= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
        ", [$activeStatuses], [\Doctrine\DBAL\Connection::PARAM_STR_ARRAY]);
        $defectRate30d = $activeContracts30d > 0 ? round(($lateContractsCount30d / $activeContracts30d) * 100, 1) : 0;
        $defectRateTrend = $defectRate <= $defectRate30d ? 'down' : 'up';

        // 2. Trends (Historical Debt View)

        // Total Expected from the beginning of time until the start of our chart
        $totalExpectedBefore = (float)$conn->fetchOne("
            SELECT SUM(amount) FROM payment_schedule 
            WHERE deleted_at IS NULL AND expected_date < :start
        ", ['start' => $startDate]);

        // Total Paid from the beginning of time until the start of our chart
        $totalPaidBefore = (float)$conn->fetchOne("
            SELECT SUM(amount) FROM payment 
            WHERE deleted_at IS NULL AND status IN ('VALIDÉ', 'VALIDATED') AND date < :start
              AND type NOT IN ('Apport Initial', 'Frais de dossier', 'RÉPARATION_CLIENT', 'FRAIS_AGENCE', 'PÉNALITÉ')
        ", ['start' => $startDate]);

        // Opening Debt Balance
        $openingDebt = max(0, $totalExpectedBefore - $totalPaidBefore);

        $datePart = $groupByYear ? '%Y' : '%Y-%m';

        // Monthly/Yearly Expected
        $sqlExpected = "
            SELECT 
                DATE_FORMAT(ps.expected_date, '{$datePart}') as period,
                SUM(ps.amount) as expected
            FROM payment_schedule ps
            WHERE ps.deleted_at IS NULL AND ps.expected_date >= :start AND ps.expected_date <= LAST_DAY(CURRENT_DATE())
            GROUP BY DATE_FORMAT(ps.expected_date, '{$datePart}')
            ORDER BY period ASC
        ";
        $expectedRaw = $conn->fetchAllAssociative($sqlExpected, ['start' => $startDate]);

        // Monthly/Yearly Paid
        $sqlPaid = "
            SELECT 
                DATE_FORMAT(p.date, '{$datePart}') as period,
                SUM(p.amount) as paid
            FROM payment p
            WHERE p.deleted_at IS NULL 
              AND p.status IN ('VALIDÉ', 'VALIDATED')
              AND p.date >= :start AND p.date <= LAST_DAY(CURRENT_DATE())
              AND p.type NOT IN ('Apport Initial', 'Frais de dossier', 'RÉPARATION_CLIENT', 'FRAIS_AGENCE', 'PÉNALITÉ')
            GROUP BY DATE_FORMAT(p.date, '{$datePart}')
            ORDER BY period ASC
        ";
        $paidRaw = $conn->fetchAllAssociative($sqlPaid, ['start' => $startDate]);

        // Map results
        $expectedByPeriod = [];
        foreach ($expectedRaw as $row)
            $expectedByPeriod[$row['period']] = (float)$row['expected'];
        $paidByPeriod = [];
        foreach ($paidRaw as $row)
            $paidByPeriod[$row['period']] = (float)$row['paid'];

        // Build data points with carryover
        $trendData = [];
        $currentDate = new \DateTime($startDate);
        $endDate = new \DateTime('last day of this month');

        $step = $groupByYear ? '+1 year' : '+1 month';
        $format = $groupByYear ? 'Y' : 'Y-m';

        $endKey = $endDate->format($format);
        $runningDebt = $openingDebt;

        $periodKey = "";
        $maxIter = 100;
        $i = 0;
        while ($i++ < $maxIter) {
            $periodKey = $currentDate->format($format);
            $periodExpected = $expectedByPeriod[$periodKey] ?? 0;
            $periodPaid = $paidByPeriod[$periodKey] ?? 0;

            // Target = Current period expected + unpaid from before
            $targetForPeriod = $periodExpected + $runningDebt;

            $trendData[] = [
                'month' => $periodKey,
                'expected' => $targetForPeriod,
                'paid' => $periodPaid
            ];

            // Next period starting debt
            $runningDebt = max(0, $targetForPeriod - $periodPaid);

            if ($periodKey === $endKey)
                break;

            $currentDate->modify($step);
        }

        // 3. Imminent Risks (Late payments > 10 days)
        // L'exposition (value) doit être la somme totale des impayés (en retard ou partiel) du contrat,
        // pas seulement ceux qui datent de plus de 10 jours.
        $sqlRisks = "
            SELECT 
                c.reference as id, 
                CONCAT(cl.last_name, ' ', cl.first_name) as client,
                'Retard de paiement critique' as issue,
                'danger' as severity,
                (
                    SELECT SUM(ps_all.amount - COALESCE(ps_all.paid_amount, 0))
                    FROM payment_schedule ps_all
                    WHERE ps_all.contract_id = c.id
                    AND ps_all.deleted_at IS NULL
                    AND ps_all.status IN ('En retard', 'Partiel')
                ) as value,
                COALESCE(
                    (SELECT CONCAT(v.marque, ' ', v.modele, ' (', v.immatriculation, ')') FROM vehicle v WHERE v.id = c.vehicle_id),
                    (
                        SELECT GROUP_CONCAT(CONCAT(v.marque, ' ', v.modele, ' (', v.immatriculation, ')') SEPARATOR ', ')
                        FROM contract_vehicle_demand cvd
                        JOIN contract_vehicle_demand_vehicle cvdv ON cvdv.contract_vehicle_demand_id = cvd.id
                        JOIN vehicle v ON v.id = cvdv.vehicle_id
                        WHERE cvd.contract_id = c.id
                    ),
                    'Véhicule Engagé'
                ) as vehicle_info
            FROM contract c
            JOIN client cl ON c.client_id = cl.id
            JOIN payment_schedule ps ON ps.contract_id = c.id
            WHERE ps.deleted_at IS NULL 
            AND ps.status IN ('En retard', 'Partiel') 
            AND ps.expected_date < DATE_SUB(CURRENT_DATE(), INTERVAL 10 DAY)
            GROUP BY c.id
            ORDER BY value DESC
            LIMIT 5
        ";
        $risksRaw = $conn->fetchAllAssociative($sqlRisks);
        $risks = [];
        foreach ($risksRaw as $r) {
            $risks[] = [
                'id' => $r['id'],
                'client' => $r['client'],
                'issue' => $r['issue'],
                'severity' => $r['severity'],
                'value' => (float)$r['value'],
                'vehicle_info' => $r['vehicle_info'] ?? 'Véhicule Engagé'
            ];
        }

        return [
            'kpis' => [
                'totalContracts' => $totalContracts,
                'activeContracts' => $activeContracts,
                'totalContractsGrowth' => $totalContractsGrowth,
                'mrr' => $mrr,
                'mrrGrowth' => $mrrGrowth,
                'defectRate' => $defectRate,
                'defectRateTrend' => $defectRateTrend
            ],
            'trends' => [
                'cashflow' => $trendData
            ],
            'imminentRisks' => $risks
        ];
    }

    public function getPenaltySummary(Contract $contract): array
    {
        $conn = $this->getEntityManager()->getConnection();

        // Sum total amount and paid amount directly from DB for the contract
        $sql = "
            SELECT 
                SUM(COALESCE(amount, 0)) as total,
                SUM(COALESCE(paid_amount, 0)) as paid
            FROM penalty
            WHERE contract_id = :contractId AND deleted_at IS NULL
        ";

        $results = $conn->fetchAssociative($sql, ['contractId' => $contract->getId()]);

        $summary = [
            'total' => (float)($results['total'] ?? 0),
            'paid' => (float)($results['paid'] ?? 0),
            'pending' => 0,
            'waived' => 0
        ];

        // Calculate waived (ANNULÉ/REMISE)
        $sqlWaived = "
            SELECT SUM(COALESCE(amount, 0)) as waived
            FROM penalty
            WHERE contract_id = :contractId AND deleted_at IS NULL
            AND (UPPER(status) LIKE 'ANNUL%' OR UPPER(status) LIKE 'REMISE%' OR UPPER(status) LIKE 'WAIVED%')
        ";
        $summary['waived'] = (float)$conn->fetchOne($sqlWaived, ['contractId' => $contract->getId()]);

        // Pending is what remains after payments and waivers
        $summary['pending'] = max(0, $summary['total'] - $summary['paid'] - $summary['waived']);

        return $summary;
    }
}