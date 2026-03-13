<?php

namespace App\Manager\Client;

use App\Entity\Client\Contract;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Client\ContractRepository;

class ContractManager
{
    private $em;
    private $contractRepository;

    public function __construct(
        EntityManagerInterface $em,
        ContractRepository $contractRepository
    ) {
        $this->em = $em;
        $this->contractRepository = $contractRepository;
    }

    public function create(object $data): Contract
    {
        // To be implemented
        return new Contract();
    }

    public function update(string $uuid, object $data): Contract
    {
        // To be implemented
        return new Contract();
    }

    public function delete(Contract $contract): Contract
    {
        // To be implemented
        return $contract;
    }
}
