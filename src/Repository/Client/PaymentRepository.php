<?php

namespace App\Repository\Client;

use App\Entity\Client\Payment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Payment>
 *
 * @method Payment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Payment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Payment[]    findAll()
 * @method Payment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Payment $entity, bool $flush = true): void
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
    public function remove(Payment $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findByFilters(array $filters): array
    {
        $limit = isset($filters['count']) ? (int)$filters['count'] : 20;
        $search = $filters['search'] ?? null;
        $method = $filters['method'] ?? null;
        $status = $filters['status'] ?? null;
        $dateMin = $filters['dateMin'] ?? null;
        $dateMax = $filters['dateMax'] ?? null;
        $amountMin = $filters['amountMin'] ?? null;
        $amountMax = $filters['amountMax'] ?? null;

        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.client', 'c')
            ->leftJoin('p.contract', 'ct')
            ->leftJoin('ct.vehicle', 'vDirect')
            ->leftJoin('ct.vehicleDemands', 'vd')
            ->leftJoin('vd.assignedVehicles', 'vAssigned')
            ->addSelect('c', 'ct', 'vDirect', 'vd', 'vAssigned');

        if ($search) {
            $qb->andWhere('p.reference LIKE :search OR p.observation LIKE :search OR c.lastName LIKE :search OR c.firstName LIKE :search OR ct.reference LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($method) {
            $qb->andWhere('p.method = :method')
                ->setParameter('method', $method);
        }

        if ($status) {
            $qb->andWhere('p.status = :status')
                ->setParameter('status', $status);
        }

        if ($dateMin) {
            $qb->andWhere('p.date >= :dateMin')
                ->setParameter('dateMin', $dateMin);
        }

        if ($dateMax) {
            $qb->andWhere('p.date <= :dateMax')
                ->setParameter('dateMax', $dateMax);
        }

        if ($amountMin !== null) {
            $qb->andWhere('p.amount >= :amountMin')
                ->setParameter('amountMin', $amountMin);
        }

        if ($amountMax !== null) {
            $qb->andWhere('p.amount <= :amountMax')
                ->setParameter('amountMax', $amountMax);
        }

        // Prioritized Vehicle Filter (Consolidation of vehicleId and vehicle UUID/Plate)
        if (isset($filters['vehicleId']) || isset($filters['vehicle'])) {
            $vIds = [];
            if (isset($filters['vehicleId']) && !empty($filters['vehicleId'])) {
                // Technical ID is prioritized
                $vIds = [$filters['vehicleId']];
            }
            else {
                // Fallback to UUID/Plate identification
                $vSearch = trim($filters['vehicle']);
                $cleanVId = str_replace('/', '', $vSearch);
                $vRes = $this->_em->getRepository(\App\Entity\Client\Vehicle::class)
                    ->createQueryBuilder('v_sub')
                    ->select('v_sub.id')
                    ->where('v_sub.uuid = :vE OR v_sub.immatriculation = :vE OR v_sub.uuid LIKE :vL OR v_sub.immatriculation LIKE :vL')
                    ->setParameter('vE', $vSearch)
                    ->setParameter('vL', '%' . $cleanVId . '%')
                    ->getQuery()
                    ->getScalarResult();
                $vIds = array_column($vRes, 'id');
            }

            if (!empty($vIds)) {
                // Find all Contracts related to these vehicles
                $cIds = $this->_em->getRepository(\App\Entity\Client\Contract::class)
                    ->createQueryBuilder('ct_sub')
                    ->select('ct_sub.id')
                    ->leftJoin('ct_sub.vehicleDemands', 'vd_sub')
                    ->leftJoin('vd_sub.assignedVehicles', 'vas_sub')
                    ->where('ct_sub.vehicle IN (:vs) OR vas_sub.id IN (:vs)')
                    ->setParameter('vs', $vIds)
                    ->getQuery()
                    ->getScalarResult();
                $cIds = array_column($cIds, 'id');

                if (!empty($cIds)) {
                    $qb->andWhere('p.contract IN (:cts)')
                        ->setParameter('cts', $cIds);
                }
                else {
                    $qb->andWhere('1 = 0'); // Contracts not found
                }
            }
            else {
                $qb->andWhere('1 = 0'); // Vehicles not found
            }
        }

        return $qb->orderBy('p.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getDashboardMetrics(int $months = 6): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $startDate = (new \DateTime())->modify("-{$months} months")->format('Y-m-01');
        $groupByYear = $months > 36;

        // Expected MRR for the selected period
        $mrr = (float)$conn->fetchOne("
            SELECT SUM(amount) FROM payment_schedule 
            WHERE deleted_at IS NULL AND expected_date >= :start AND expected_date <= LAST_DAY(CURRENT_DATE())
        ", ['start' => $startDate]);

        // Collected MRR for the selected period
        $mrrCollected = (float)$conn->fetchOne("
            SELECT SUM(paid_amount) FROM payment_schedule 
            WHERE deleted_at IS NULL AND expected_date >= :start AND expected_date <= LAST_DAY(CURRENT_DATE())
        ", ['start' => $startDate]);

        // Total Overdue (All time)
        $totalOverdue = (float)$conn->fetchOne("
            SELECT SUM(amount - COALESCE(paid_amount, 0)) FROM payment_schedule 
            WHERE deleted_at IS NULL AND status IN ('En retard', 'Partiel') AND expected_date < CURRENT_DATE()
        ");

        $overdueCount = (int)$conn->fetchOne("
            SELECT COUNT(DISTINCT contract_id) FROM payment_schedule 
            WHERE deleted_at IS NULL AND status IN ('En retard', 'Partiel') AND expected_date < CURRENT_DATE()
        ");

        // Next Month Forecast
        $nextMonthStart = (new \DateTime())->modify("+1 month")->format('Y-m-01');
        $nextMonthForecast = (float)$conn->fetchOne("
            SELECT SUM(amount) FROM payment_schedule 
            WHERE deleted_at IS NULL AND expected_date >= :start AND expected_date <= LAST_DAY(:start)
        ", ['start' => $nextMonthStart]);

        // Cash Balance (Simplified: Total Validated Payments)
        $cashBalance = (float)$conn->fetchOne("
            SELECT SUM(amount) FROM payment 
            WHERE status IN ('VALIDÉ', 'VALIDATED') AND deleted_at IS NULL
        ");

        // 2. Cashflow Trends (Historical Debt View)

        // Total Expected (Schedules) from the beginning of time until the start of our chart
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

        // Opening Debt Balance (What was owed at the moment the chart starts)
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

        // Map results for iterative calculation
        $expectedByPeriod = [];
        foreach ($expectedRaw as $row)
            $expectedByPeriod[$row['period']] = (float)$row['expected'];

        $paidByPeriod = [];
        foreach ($paidRaw as $row)
            $paidByPeriod[$row['period']] = (float)$row['paid'];

        // Build data points
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

            // Target = Current period expected + everything unpaid from BEFORE
            $targetForPeriod = $periodExpected + $runningDebt;

            $trendData[] = [
                'month' => $periodKey,
                'expected' => $targetForPeriod,
                'paid' => $periodPaid
            ];

            // Update remaining debt for the next period's starting balance
            $runningDebt = max(0, $targetForPeriod - $periodPaid);

            if ($periodKey === $endKey)
                break;

            $currentDate->modify($step);
        }

        // 3. Payment Methods (Donut)
        $sqlMethods = "
            SELECT COALESCE(method, 'Espèces') as name, COUNT(id) as value
            FROM payment
            WHERE deleted_at IS NULL AND status IN ('VALIDÉ', 'VALIDATED')
            GROUP BY name
        ";
        $methodsData = $conn->fetchAllAssociative($sqlMethods);

        // 4. Recent Payments
        $sqlRecent = "
            SELECT 
                p.uuid as id, c.first_name as firstName, c.last_name as lastName,
                ctr.reference as contractId, p.amount, p.date, 
                COALESCE(p.method, 'Espèces') as method, p.status,
                p.reference, p.recorded_by as recordedBy, p.created_at as createdAt
            FROM payment p
            JOIN client c ON p.client_id = c.id
            LEFT JOIN contract ctr ON p.contract_id = ctr.id
            WHERE p.deleted_at IS NULL
            ORDER BY p.date DESC, p.id DESC
            LIMIT 10
        ";
        $recentPaymentsRaw = $conn->fetchAllAssociative($sqlRecent);
        $recentPayments = [];
        foreach ($recentPaymentsRaw as $rp) {
            // Convert binary UUID if necessary
            $id = $rp['id'];
            if (strlen($id) === 16) {
                try {
                    $id = \Ramsey\Uuid\Uuid::fromBytes($id)->toString();
                }
                catch (\Exception $e) {
                    $id = bin2hex($id);
                }
            }

            $recentPayments[] = [
                'id' => $id,
                'client' => $rp['lastName'] . ' ' . $rp['firstName'],
                'contractId' => $rp['contractId'],
                'amount' => (float)$rp['amount'],
                'date' => $rp['date'],
                'method' => $rp['method'],
                'status' => $rp['status'],
                'reference' => $rp['reference'],
                'recordedBy' => $rp['recordedBy'],
                'createdAt' => $rp['createdAt']
            ];
        }

        return [
            'kpis' => [
                'mrr' => $mrr,
                'mrrCollected' => $mrrCollected,
                'collectionRate' => $mrr > 0 ? round(($mrrCollected / $mrr) * 100, 1) : 0,
                'totalOverdue' => $totalOverdue,
                'overdueCount' => $overdueCount,
                'nextMonthForecast' => $nextMonthForecast,
                'cashBalance' => $cashBalance,
                'avgPaymentDelay' => (float)$conn->fetchOne("
                    SELECT COALESCE(AVG(DATEDIFF(paid_at, expected_date)), 0)
                    FROM payment_schedule
                    WHERE status = 'Payé' AND deleted_at IS NULL AND expected_date >= :start AND paid_at IS NOT NULL
                ", ['start' => $startDate]),
                'activePenalties' => (int)$conn->fetchOne("
                    SELECT COUNT(id) FROM penalty 
                    WHERE deleted_at IS NULL AND status NOT IN ('PAYÉ', 'SOLDÉ', 'ANNULÉ', 'WAIVED')
                "),
                'penaltiesAmount' => (float)$conn->fetchOne("
                    SELECT SUM(amount - COALESCE(paid_amount, 0)) FROM penalty 
                    WHERE deleted_at IS NULL AND status NOT IN ('PAYÉ', 'SOLDÉ', 'ANNULÉ', 'WAIVED')
                ")
            ],
            'trends' => [
                'cashflow' => $trendData
            ],
            'methods' => $methodsData,
            'recentPayments' => $recentPayments,
            'totalPaymentsCount' => (int)$conn->fetchOne("SELECT COUNT(id) FROM payment WHERE deleted_at IS NULL")
        ];
    }
}