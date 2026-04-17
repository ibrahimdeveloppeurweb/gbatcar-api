<?php

namespace App\Repository\Client;

use App\Entity\Client\Maintenance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Maintenance>
 *
 * @method Maintenance|null find($id, $lockMode = null, $lockVersion = null)
 * @method Maintenance|null findOneBy(array $criteria, array $orderBy = null)
 * @method Maintenance[]    findAll()
 * @method Maintenance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaintenanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Maintenance::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Maintenance $entity, bool $flush = true): void
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
    public function remove(Maintenance $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function getDashboardMetrics(array $filters = []): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $months = isset($filters['months']) ? (int)$filters['months'] : 6;
        $startDate = new \DateTimeImmutable("-$months months 00:00:00");
        $now = new \DateTimeImmutable();
        $startOfMonth = new \DateTimeImmutable('first day of this month 00:00:00');

        // 1. KPIs
        $qb = $this->createQueryBuilder('m');
        $totalInterventions = (int)$this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.dateIntervention >= :start')
            ->setParameter('start', $startDate)
            ->getQuery()->getSingleScalarResult();

        $interventionsThisMonth = (int)$this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.dateIntervention >= :start')
            ->setParameter('start', $startOfMonth)
            ->getQuery()->getSingleScalarResult();

        $pendingInterventions = (int)$this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.status IN (:statuses)')
            ->setParameter('statuses', ['Planifié', 'En cours', 'En attente'])
            ->getQuery()->getSingleScalarResult();

        $completedInterventions = (int)$this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.status = :status')
            ->setParameter('status', 'Terminé')
            ->getQuery()->getSingleScalarResult();

        $plannedInterventions = (int)$this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.status = :status')
            ->setParameter('status', 'Planifié')
            ->getQuery()->getSingleScalarResult();

        $inProgressInterventions = (int)$this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.status = :status')
            ->setParameter('status', 'En cours')
            ->getQuery()->getSingleScalarResult();

        $monthlyCost = (float)$this->createQueryBuilder('m')
            ->select('SUM(m.cost)')
            ->where('m.dateIntervention >= :start')
            ->setParameter('start', $startOfMonth)
            ->getQuery()->getSingleScalarResult();

        $totalCostYTD = (float)$this->createQueryBuilder('m')
            ->select('SUM(m.cost)')
            ->where('m.dateIntervention >= :start')
            ->setParameter('start', new \DateTimeImmutable('first day of January this year'))
            ->getQuery()->getSingleScalarResult();

        $vehiclesInShop = (int)$this->createQueryBuilder('m')
            ->select('COUNT(DISTINCT m.vehicle)')
            ->where('m.status = :status')
            ->setParameter('status', 'En cours')
            ->getQuery()->getSingleScalarResult();

        // Avg Repair Days (Dynamic Cycle Time: average of (endDate or now) - startDate)
        $avgRepairDays = (float)$conn->fetchOne("
            SELECT AVG(TIMESTAMPDIFF(SECOND, start_date, COALESCE(end_date, CURRENT_TIMESTAMP))) / 86400
            FROM maintenance
            WHERE start_date IS NOT NULL AND date_intervention >= :start AND deleted_at IS NULL
        ", ['start' => $startOfMonth->format('Y-01-01')]); // YTD calculation for average consistency
        if (!$avgRepairDays)
            $avgRepairDays = 0;

        // 2. Alerts (from MaintenanceAlert)
        $alertCount = (int)$conn->fetchOne("SELECT COUNT(*) FROM maintenance_alert WHERE status != 'Clôturé' AND deleted_at IS NULL");
        $criticalAlerts = (int)$conn->fetchOne("SELECT COUNT(*) FROM maintenance_alert WHERE severity = 'danger' AND status != 'Clôturé' AND deleted_at IS NULL");

        // 3. Distribution (Intervention Types)
        $sqlDist = "
            SELECT mt.name as label, COUNT(m.id) as value
            FROM maintenance m
            JOIN maintenance_type mt ON m.maintenance_type_id = mt.id
            WHERE m.date_intervention >= :start AND m.deleted_at IS NULL
            GROUP BY mt.id
            ORDER BY value DESC
            LIMIT 5
        ";
        $distribution = $conn->fetchAllAssociative($sqlDist, ['start' => $startDate->format('Y-m-d H:i:s')]);

        // 4. Cost Trends (Cumulative Budget model following Payment logic)
        $groupByYear = $months > 36;
        $dateFormat = $groupByYear ? '%Y' : '%Y-%m';
        $step = $groupByYear ? '+1 year' : '+1 month';

        $sqlRawTrends = "
            SELECT 
                DATE_FORMAT(date_intervention, '$dateFormat') as period,
                SUM(cost) as total_cost
            FROM maintenance
            WHERE date_intervention >= DATE_SUB(CURRENT_DATE(), INTERVAL :months MONTH)
              AND deleted_at IS NULL
            GROUP BY period
            ORDER BY period ASC
        ";
        $rawTrends = $conn->fetchAllAssociative($sqlRawTrends, ['months' => $months]);
        $trendsMap = [];
        foreach ($rawTrends as $row) {
            $trendsMap[$row['period']] = (float)$row['total_cost'];
        }

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

        $trends = [];
        $currentDate = (new \DateTime())->modify("-{$months} months")->modify('first day of this month');
        $endDate = new \DateTime('last day of this month');
        $endKey = $endDate->format($groupByYear ? 'Y' : 'Y-m');

        $accumulatedBudget = 0;
        $accumulatedCost = 0;
        $periodicBudget = 200000; // Default fallback

        for ($i = 0; $i < 100; $i++) {
            $periodKey = $currentDate->format($groupByYear ? 'Y' : 'Y-m');
            $periodCost = $trendsMap[$periodKey] ?? 0;

            // Get budget for this period (custom or default)
            $pBudget = 0;
            if ($groupByYear) {
                for ($m = 1; $m <= 12; $m++) {
                    $mKey = $periodKey . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
                    $pBudget += $budgetMap[$mKey] ?? 200000;
                }
            }
            else {
                $pBudget = $budgetMap[$periodKey] ?? 200000;
            }

            $periodicBudget = $pBudget;
            $accumulatedBudget += $periodicBudget;
            $accumulatedCost += $periodCost;

            $trends[] = [
                'month' => $periodKey,
                'expected' => $periodicBudget, // Periodic Budget
                'paid' => $periodCost // Periodic Actual Cost
            ];

            if ($periodKey === $endKey)
                break;
            $currentDate->modify($step);
        }

        // 5. Recent Items
        $recentInterventions = $this->findByFilters(['limit' => 5]);
        $sqlRecentAlerts = "SELECT id, reference, type, severity, status, repair_cost as repairCost, created_at as createdAt FROM maintenance_alert WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT 5";
        $accidents = $conn->fetchAllAssociative($sqlRecentAlerts);

        return [
            'stats' => [
                'totalInterventions' => $totalInterventions,
                'interventionsThisMonth' => $interventionsThisMonth,
                'pendingInterventions' => $pendingInterventions,
                'plannedInterventions' => $plannedInterventions,
                'inProgressInterventions' => $inProgressInterventions,
                'completedInterventions' => $completedInterventions,
                'avgRepairDays' => $avgRepairDays,
                'totalCostYTD' => $totalCostYTD,
                'monthlyCost' => $monthlyCost,
                'budgetMonthly' => $periodicBudget,
                'activeAlerts' => $alertCount,
                'criticalAlerts' => $criticalAlerts,
                'avgCostPerIntervention' => $totalInterventions > 0 ? $totalCostYTD / $totalInterventions : 0,
                'vehiclesInShop' => $vehiclesInShop
            ],
            'distribution' => $distribution,
            'trends' => $trends,
            'recentInterventions' => $recentInterventions,
            'accidents' => $accidents
        ];
    }

    public function findByFilters(array $filters = []): array
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.vehicle', 'v')
            ->addSelect('v')
            ->orderBy('m.createdAt', 'DESC');

        // Full-text search across reference, type, provider
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $qb->andWhere(
                $qb->expr()->orX(
                $qb->expr()->like('m.reference', ':search'),
                $qb->expr()->like('m.type', ':search'),
                $qb->expr()->like('m.provider', ':search'),
                $qb->expr()->like('v.immatriculation', ':search'),
                $qb->expr()->like('v.marque', ':search'),
                $qb->expr()->like('v.modele', ':search')
            )
            )->setParameter('search', $search);
        }

        if (!empty($filters['status'])) {
            $qb->andWhere('m.status = :status')->setParameter('status', $filters['status']);
        }

        if (!empty($filters['dateMin'])) {
            $qb->andWhere('m.dateIntervention >= :dateMin')
                ->setParameter('dateMin', new \DateTimeImmutable($filters['dateMin']));
        }

        if (!empty($filters['dateMax'])) {
            $qb->andWhere('m.dateIntervention <= :dateMax')
                ->setParameter('dateMax', new \DateTimeImmutable($filters['dateMax'] . ' 23:59:59'));
        }

        if (isset($filters['costMin']) && $filters['costMin'] !== '') {
            $qb->andWhere('m.cost >= :costMin')->setParameter('costMin', (float)$filters['costMin']);
        }

        if (isset($filters['costMax']) && $filters['costMax'] !== '') {
            $qb->andWhere('m.cost <= :costMax')->setParameter('costMax', (float)$filters['costMax']);
        }

        if (!empty($filters['limit'])) {
            $qb->setMaxResults((int)$filters['limit']);
        }

        return $qb->getQuery()->getResult();
    }
}