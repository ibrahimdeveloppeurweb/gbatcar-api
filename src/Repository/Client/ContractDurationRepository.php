<?php

namespace App\Repository\Client;

use App\Entity\Client\ContractDuration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ContractDuration|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContractDuration|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContractDuration[]    findAll()
 * @method ContractDuration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContractDurationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContractDuration::class);
    }
}