<?php

namespace App\Manager\Client;

use App\Entity\Client\Penalty;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Client\PenaltyRepository;

class PenaltyManager
{
    private $em;
    private $penaltyRepository;

    public function __construct(
        EntityManagerInterface $em,
        PenaltyRepository $penaltyRepository
    ) {
        $this->em = $em;
        $this->penaltyRepository = $penaltyRepository;
    }

    public function create(object $data): Penalty
    {
        // To be implemented
        return new Penalty();
    }

    public function update(string $uuid, object $data): Penalty
    {
        // To be implemented
        return new Penalty();
    }

    public function delete(Penalty $penalty): Penalty
    {
        // To be implemented
        return $penalty;
    }
}
