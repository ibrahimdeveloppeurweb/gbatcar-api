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
            ->leftJoin('c.contracts', 'ct')
            ->addSelect('v')
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
            elseif ($status === 'En Attente Validation' || $status === 'En attente de Validation') {
                $qb->andWhere('c.status = :status')
                    ->setParameter('status', 'En attente de Validation');
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
                    ->setParameter('fStatuses', ['En attente de Validation', 'Prospect']);
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

    public function getDashboardMetrics(): array
    {
        $conn = $this->_em->getConnection();

        // 1. Global KPIs
        $totalClients = (int)$conn->fetchOne('SELECT COUNT(id) FROM client WHERE deleted_at IS NULL');
        $activeClients = (int)$conn->fetchOne("SELECT COUNT(id) FROM client WHERE deleted_at IS NULL AND status IN ('Dossier Approuvé', 'En Cours de Contrat')");
        $lateClients = (int)$conn->fetchOne("SELECT COUNT(id) FROM client WHERE deleted_at IS NULL AND status = 'Litige / Bloqué'");
        $portfolioValue = (float)$conn->fetchOne("SELECT SUM(unpaid_amount) FROM client WHERE deleted_at IS NULL");
        $newThisMonth = (int)$conn->fetchOne("SELECT COUNT(id) FROM client WHERE deleted_at IS NULL AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");

        // 2. Status Distribution (Donut Chart)
        $statusCounts = $conn->fetchAllAssociative("
            SELECT status, COUNT(id) as count 
            FROM client 
            WHERE deleted_at IS NULL 
            GROUP BY status
        ");

        $distribution = [
            'Actifs' => 0,
            'En Retard' => 0,
            'En Attente' => 0,
            'Inactifs' => 0
        ];

        foreach ($statusCounts as $row) {
            if (in_array($row['status'], ['Dossier Approuvé', 'En Cours de Contrat'])) {
                $distribution['Actifs'] += (int)$row['count'];
            }
            elseif ($row['status'] === 'Litige / Bloqué') {
                $distribution['En Retard'] += (int)$row['count'];
            }
            elseif (in_array($row['status'], ['En attente de Validation', 'Prospect'])) {
                $distribution['En Attente'] += (int)$row['count'];
            }
            elseif ($row['status'] === 'Inactif') {
                $distribution['Inactifs'] += (int)$row['count'];
            }
        }

        // 3. Evolution last 6 months (Bar Chart)
        $sixMonthsAgo = (new \DateTime('-5 months'))->modify('first day of this month')->format('Y-m-d 00:00:00');

        $newClientsTrend = $conn->fetchAllAssociative("
            SELECT 
                MONTH(created_at) as month,
                YEAR(created_at) as year,
                COUNT(id) as count
            FROM client 
            WHERE created_at >= :sixMonthsAgo
            GROUP BY year, month
            ORDER BY year ASC, month ASC
        ", ['sixMonthsAgo' => $sixMonthsAgo]);

        $lostClientsTrend = $conn->fetchAllAssociative("
            SELECT 
                MONTH(updated_at) as month,
                YEAR(updated_at) as year,
                COUNT(id) as count
            FROM client 
            WHERE status = 'Inactif' AND updated_at >= :sixMonthsAgo
            GROUP BY year, month
            ORDER BY year ASC, month ASC
        ", ['sixMonthsAgo' => $sixMonthsAgo]);

        // 4. Risky Clients (List)
        $riskyClients = $this->createQueryBuilder('c')
            ->where('c.unpaidAmount > 0')
            ->andWhere('c.deletedAt IS NULL')
            ->orderBy('c.unpaidAmount', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        // 5. New Clients (List)
        $newClients = $this->createQueryBuilder('c')
            ->where('c.deletedAt IS NULL')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        return [
            'kpis' => [
                'totalClients' => $totalClients,
                'newThisMonth' => $newThisMonth,
                'activeClients' => $activeClients,
                'lateClients' => $lateClients,
                'portfolioValue' => $portfolioValue
            ],
            'distribution' => $distribution,
            'trends' => [
                'new' => $newClientsTrend,
                'lost' => $lostClientsTrend
            ],
            'riskyClients' => $riskyClients,
            'newClients' => $newClients
        ];
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