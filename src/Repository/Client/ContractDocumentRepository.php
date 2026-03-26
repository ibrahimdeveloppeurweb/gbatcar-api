<?php

namespace App\Repository\Client;

use App\Entity\Client\ContractDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContractDocument>
 *
 * @method ContractDocument|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContractDocument|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContractDocument[]    findAll()
 * @method ContractDocument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContractDocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContractDocument::class);
    }
}