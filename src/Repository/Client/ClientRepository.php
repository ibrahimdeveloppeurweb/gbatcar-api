<?php

namespace App\Repository\Client;

use App\Entity\Client\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Client>
 *
 * @method Client|null find($id, $lockMode = null, $lockVersion = null)
 * @method Client|null findOneBy(array $criteria, array $orderBy = null)
 * @method Client[]    findAll()
 * @method Client[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Client $entity, bool $flush = true): void
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
    public function remove(Client $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.vehicles', 'v')
            ->leftJoin('v.compliance', 'comp')
            ->leftJoin('c.contracts', 'ct')
            ->addSelect('v')
            ->addSelect('comp')
            ->addSelect('ct');

        if (!empty($filters['search'])) {
            $search = '%' . mb_strtolower($filters['search']) . '%';
            $qb->andWhere('LOWER(c.firstName) LIKE :search OR LOWER(c.lastName) LIKE :search OR LOWER(c.phone) LIKE :search OR LOWER(c.email) LIKE :search OR LOWER(c.idNumber) LIKE :search OR LOWER(c.uuid) LIKE :search')
                ->setParameter('search', $search);
        }

        if (!empty($filters['status'])) {
            $status = $filters['status'];
            if ($status === 'Actif' || $status === 'Dossier Approuvé') {
                $qb->andWhere('c.status IN (:statuses)')
                    ->setParameter('statuses', ['Dossier Approuvé', 'En Cours de Contrat']);
            }
            elseif ($status === 'Actif (Retard)') {
                $qb->andWhere('c.status IN (:statuses)')
                    ->andWhere('c.unpaidAmount > 0')
                    ->setParameter('statuses', ['Dossier Approuvé', 'En Cours de Contrat']);
            }
            elseif ($status === 'En Attente Validation' || $status === 'En attente de Validation' || $status === 'Dossier Validé') {
                $qb->andWhere('c.status IN (:statuses)')
                    ->setParameter('statuses', ['En attente de Validation', 'Dossier Validé']);
            }
            else {
                $qb->andWhere('c.status = :status')
                    ->setParameter('status', $status);
            }
        }

        if (!empty($filters['vehicle'])) {
            $vehicle = '%' . mb_strtolower($filters['vehicle']) . '%';
            $qb->andWhere('LOWER(v.marque) LIKE :vehicle OR LOWER(v.modele) LIKE :vehicle OR LOWER(v.immatriculation) LIKE :vehicle')
                ->setParameter('vehicle', $vehicle);
        }

        if (!empty($filters['financialStatus'])) {
            $fStatus = $filters['financialStatus'];
            if ($fStatus === 'À jour / Soldé') {
                $qb->andWhere('(c.unpaidAmount IS NULL OR c.unpaidAmount = 0) AND c.status != :waitStatus')
                    ->setParameter('waitStatus', 'En attente de Validation');
            }
            elseif ($fStatus === 'Impayé') {
                $qb->andWhere('c.unpaidAmount > 0');
            }
            elseif ($fStatus === 'Aucun (En Attente)') {
                $qb->andWhere('c.status IN (:fStatuses)')
                    ->setParameter('fStatuses', ['En attente de Validation', 'Prospect', 'Dossier Validé']);
            }
        }

        if (array_key_exists('minDebt', $filters) && $filters['minDebt'] !== null && $filters['minDebt'] !== '' && $filters['minDebt'] !== 'null') {
            $qb->andWhere('c.unpaidAmount >= :minDebt')
                ->setParameter('minDebt', $filters['minDebt']);
        }

        if (array_key_exists('maxDebt', $filters) && $filters['maxDebt'] !== null && $filters['maxDebt'] !== '' && $filters['maxDebt'] !== 'null') {
            $qb->andWhere('c.unpaidAmount <= :maxDebt')
                ->setParameter('maxDebt', $filters['maxDebt']);
        }

        $qb->orderBy('c.id', 'DESC');

        if (!empty($filters['count']) && is_numeric($filters['count'])) {
            $qb->setMaxResults((int)$filters['count']);
        }
        else {
            $qb->setMaxResults(20);
        }

        return $qb->getQuery()->getResult();
    }

    public function getDashboardMetrics(int $months = 6): array
    {
        $conn = $this->_em->getConnection();

        $monthsToSubtract = max(0, $months - 1);
        $startDate = (new \DateTime("-{$monthsToSubtract} months"))->modify('first day of this month')->format('Y-m-d 00:00:00');

        // 1. & 2. Global KPIs and Status Distribution (Filtered by timeframe)
        // Build classification based on actual Contracts and Payment Schedules bounds
        $sqlClassification = "
            SELECT 
                CASE
                    WHEN EXISTS (
                        SELECT 1 FROM contract ctr
                        WHERE ctr.client_id = c.id 
                          AND ctr.status IN ('ACTIVE', 'EN COURS', 'EN_COURS', 'VALIDÉ', 'Actif', 'En cours', 'Validé')
                    ) THEN 
                        CASE 
                            WHEN EXISTS (
                                SELECT 1 FROM payment_schedule ps
                                INNER JOIN contract ctr2 ON ps.contract_id = ctr2.id
                                WHERE ctr2.client_id = c.id
                                  AND ctr2.status IN ('ACTIVE', 'EN COURS', 'EN_COURS', 'VALIDÉ', 'Actif', 'En cours', 'Validé')
                                  AND ps.expected_date < CURRENT_DATE()
                                  AND ps.status IN ('En retard', 'Partiel')
                            ) THEN 'En Retard'
                            ELSE 'Actifs'
                        END
                        
                    WHEN EXISTS (
                        SELECT 1 FROM contract ctr
                        WHERE ctr.client_id = c.id 
                          AND ctr.status IN ('NEW', 'PENDING', 'EN ATTENTE', 'En Attente', 'Nouveau', 'Prospect')
                    ) THEN 'En Attente'
                    
                    WHEN EXISTS (
                        SELECT 1 FROM contract ctr
                        WHERE ctr.client_id = c.id 
                          AND ctr.status IN ('TERMINÉ', 'SOLDÉ', 'Solder', 'ROMPU', 'ANNULÉ', 'Résilié')
                    ) 
                    OR EXISTS (
                        SELECT 1 FROM vehicle v 
                        JOIN contract ctr3 ON v.id = ctr3.vehicle_id 
                        WHERE ctr3.client_id = c.id AND v.statut = 'Vendu'
                    )
                    OR c.status = 'Inactif' THEN 'Inactifs'
                    
                    ELSE 'En Attente'
                END as class_status,
                COUNT(c.id) as count
            FROM client c
            WHERE c.deleted_at IS NULL AND c.created_at >= :start
            GROUP BY class_status
        ";

        $statusCounts = $conn->fetchAllAssociative($sqlClassification, ['start' => $startDate]);

        $distribution = [
            'Actifs' => 0,
            'En Retard' => 0,
            'En Attente' => 0,
            'Inactifs' => 0
        ];

        foreach ($statusCounts as $row) {
            $statusCat = $row['class_status'];
            if (isset($distribution[$statusCat])) {
                $distribution[$statusCat] += (int)$row['count'];
            }
        }

        $lateData = $conn->fetchAssociative("
            SELECT 
                SUM(ps.amount - ps.paid_amount) as total_late,
                COUNT(DISTINCT ctr.client_id) as late_clients_count
            FROM payment_schedule ps
            JOIN contract ctr ON ps.contract_id = ctr.id
            WHERE ps.deleted_at IS NULL 
              AND ctr.deleted_at IS NULL
              AND ps.status IN ('En retard', 'Partiel')
              AND ps.expected_date < CURRENT_DATE()
              AND ctr.start_date >= :start
        ", ['start' => $startDate]);

        $portfolioValue = (float)($lateData['total_late'] ?? 0);
        $lateClientsWithDebit = (int)($lateData['late_clients_count'] ?? 0);

        $totalClients = array_sum($distribution);
        $activeClients = $distribution['Actifs'];

        // This is always true for the current month, but we limit by start just in case 
        $newThisMonth = (int)$conn->fetchOne("SELECT COUNT(id) FROM client WHERE deleted_at IS NULL AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE()) AND created_at >= :start", ['start' => $startDate]);

        // 3. Evolution last X months (Bar Chart Cohort Analysis)
        $sqlEvolution = "
            SELECT 
                MONTH(ctr.start_date) as month,
                YEAR(ctr.start_date) as year,
                CASE 
                    WHEN ctr.status IN ('TERMINÉ', 'SOLDÉ', 'Solder') OR v.statut = 'Vendu' THEN 'Gris'
                    WHEN ctr.status IN ('ROMPU', 'ANNULÉ', 'Résilié') THEN 'Rouge'
                    WHEN ctr.status IN ('ACTIVE', 'EN COURS', 'VALIDÉ', 'EN_COURS', 'Actif', 'En cours', 'Validé', 'VALIDATED') AND NOT EXISTS (
                        SELECT 1 FROM payment_schedule ps WHERE ps.contract_id = ctr.id AND ps.expected_date < CURRENT_DATE() AND ps.status IN ('En retard', 'Partiel')
                    ) THEN 'Vert'
                    ELSE 'Autre'
                END as classification,
                COUNT(ctr.id) as count
            FROM contract ctr
            JOIN client c ON ctr.client_id = c.id
            LEFT JOIN vehicle v ON ctr.vehicle_id = v.id
            WHERE ctr.deleted_at IS NULL AND c.deleted_at IS NULL AND ctr.start_date >= :start
            GROUP BY year, month, classification
            ORDER BY year ASC, month ASC
        ";

        $evolutionData = $conn->fetchAllAssociative($sqlEvolution, ['start' => $startDate]);

        $vertTrend = [];
        $grisTrend = [];
        $rougeTrend = [];

        foreach ($evolutionData as $row) {
            $item = ['month' => $row['month'], 'year' => $row['year'], 'count' => $row['count']];
            if ($row['classification'] === 'Vert')
                $vertTrend[] = $item;
            elseif ($row['classification'] === 'Gris')
                $grisTrend[] = $item;
            elseif ($row['classification'] === 'Rouge')
                $rougeTrend[] = $item;
        }

        // 4. Risky Clients (Dynamic Delay calculation)
        $sqlRisky = "
            SELECT 
                c.id, c.uuid, c.first_name as firstName, c.last_name as lastName,
                v.marque, v.modele,
                SUM(ps.amount - ps.paid_amount) as totalDue,
                DATEDIFF(CURRENT_DATE(), MIN(ps.expected_date)) as delayDays
            FROM client c
            JOIN contract ctr ON c.id = ctr.client_id
            JOIN payment_schedule ps ON ctr.id = ps.contract_id
            LEFT JOIN vehicle v ON ctr.vehicle_id = v.id
            WHERE c.deleted_at IS NULL 
              AND ctr.deleted_at IS NULL 
              AND ps.deleted_at IS NULL
              AND ps.status IN ('En retard', 'Partiel')
              AND ps.expected_date < CURRENT_DATE()
              AND ctr.start_date >= :start
            GROUP BY c.id, v.id
            ORDER BY delayDays DESC, totalDue DESC
            LIMIT 5
        ";

        $riskyClients = $conn->fetchAllAssociative($sqlRisky, ['start' => $startDate]);

        // 5. New Clients (List) - keep this as objects since it's simple
        $newClientsRaw = $this->createQueryBuilder('c')
            ->where('c.deletedAt IS NULL')
            ->andWhere('c.createdAt >= :start')
            ->setParameter('start', $startDate)
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        $newClients = [];
        foreach ($newClientsRaw as $nc) {
            $v = $nc->getVehicles()->first();
            $newClients[] = [
                'uuid' => $nc->getUuid(),
                'firstName' => $nc->getFirstName(),
                'lastName' => $nc->getLastName(),
                'phone' => $nc->getPhone(),
                'createdAt' => $nc->getCreatedAt() ? $nc->getCreatedAt()->format('Y-m-d H:i:s') : null,
                'status' => $nc->getStatus(),
                'vehicle' => $v ? ['marque' => $v->getMarque(), 'modele' => $v->getModele()] : null
            ];
        }

        return [
            'kpis' => [
                'totalClients' => $totalClients,
                'newThisMonth' => $newThisMonth,
                'activeClients' => $activeClients,
                'lateClients' => $lateClientsWithDebit,
                'portfolioValue' => $portfolioValue
            ],
            'distribution' => $distribution,
            'trends' => [
                'vert' => $vertTrend,
                'gris' => $grisTrend,
                'rouge' => $rougeTrend
            ],
            'riskyClients' => $riskyClients,
            'newClients' => $newClients
        ];
    }
    public function findOneByUuid($uuid): ?Client
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.vehicles', 'v')
            ->leftJoin('v.compliance', 'comp')
            ->leftJoin('c.contracts', 'ct')
            ->addSelect('v', 'comp', 'ct')
            ->andWhere('c.uuid = :uuid')
            ->setParameter('uuid', $uuid, 'uuid')
            ->getQuery()
            ->getOneOrNullResult();
    }

// /**
//  * @return Client[] Returns an array of Client objects
//  */
/*
 public function findByExampleField($value)
 {
 return $this->createQueryBuilder('c')
 ->andWhere('c.exampleField = :val')
 ->setParameter('val', $value)
 ->orderBy('c.id', 'ASC')
 ->setMaxResults(10)
 ->getQuery()
 ->getResult()
 ;
 }
 */

/*
 public function findOneBySomeField($value): ?Client
 {
 return $this->createQueryBuilder('c')
 ->andWhere('c.exampleField = :val')
 ->setParameter('val', $value)
 ->getQuery()
 ->getOneOrNullResult()
 ;
 }
 */
}