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

        if (isset($filters['progressMin'])) {
            // progress = (paidAmount / totalAmount) * 100
            $qb->andWhere('(c.paidAmount / NULLIF(c.totalAmount, 0)) * 100 >= :progressMin')
                ->setParameter('progressMin', $filters['progressMin']);
        }

        if (isset($filters['progressMax'])) {
            $qb->andWhere('(c.paidAmount / NULLIF(c.totalAmount, 0)) * 100 <= :progressMax')
                ->setParameter('progressMax', $filters['progressMax']);
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

        // MRR Attendu (current month)
        $mrr = (float)$conn->fetchOne("
            SELECT SUM(amount) FROM payment_schedule 
            WHERE deleted_at IS NULL AND expected_date >= DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01') 
            AND expected_date <= LAST_DAY(CURRENT_DATE())
        ");

        // MRR (Last month)
        $lastMonthStart = (new \DateTime())->modify("-1 month")->format('Y-m-01');
        $lastMonthEnd = (new \DateTime())->modify("-1 month")->format('Y-m-t');
        $mrrLastMonth = (float)$conn->fetchOne("
            SELECT SUM(amount) FROM payment_schedule 
            WHERE deleted_at IS NULL AND expected_date >= :start AND expected_date <= :end
        ", ['start' => $lastMonthStart, 'end' => $lastMonthEnd]);
        $mrrGrowth = $mrrLastMonth > 0 ? round((($mrr - $mrrLastMonth) / $mrrLastMonth) * 100, 1) : 0;

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

        // 2. Trends (Cashflow over selected months)
        $sqlTrend = "
            SELECT 
                DATE_FORMAT(ps.expected_date, '%Y-%m') as month,
                SUM(ps.amount) as expected,
                SUM(COALESCE(ps.paid_amount, 0)) as paid
            FROM payment_schedule ps
            WHERE ps.deleted_at IS NULL AND ps.expected_date >= :start AND ps.expected_date <= LAST_DAY(CURRENT_DATE())
            GROUP BY month
            ORDER BY month ASC
        ";
        $trendData = $conn->fetchAllAssociative($sqlTrend, ['start' => $startDate]);

        // 3. Imminent Risks (Late payments > 15 days)
        $sqlRisks = "
            SELECT 
                c.reference as id, 
                CONCAT(cl.last_name, ' ', cl.first_name) as client,
                'Retard de paiement critique' as issue,
                'danger' as severity,
                SUM(ps.amount - COALESCE(ps.paid_amount, 0)) as value
            FROM contract c
            JOIN client cl ON c.client_id = cl.id
            JOIN payment_schedule ps ON ps.contract_id = c.id
            WHERE ps.deleted_at IS NULL AND ps.status IN ('En retard', 'Partiel') 
            AND ps.expected_date < DATE_SUB(CURRENT_DATE(), INTERVAL 15 DAY)
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
                'value' => (float)$r['value']
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
}