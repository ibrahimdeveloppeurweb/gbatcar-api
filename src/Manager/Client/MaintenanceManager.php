<?php

namespace App\Manager\Client;

use App\Entity\Client\Maintenance;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Client\MaintenanceRepository;

class MaintenanceManager
{
    private $em;
    private $maintenanceRepository;

    public function __construct(
        EntityManagerInterface $em,
        MaintenanceRepository $maintenanceRepository
    ) {
        $this->em = $em;
        $this->maintenanceRepository = $maintenanceRepository;
    }

    public function create(object $data): Maintenance
    {
        // To be implemented
        return new Maintenance();
    }

    public function update(string $uuid, object $data): Maintenance
    {
        // To be implemented
        return new Maintenance();
    }

    public function delete(Maintenance $maintenance): Maintenance
    {
        // To be implemented
        return $maintenance;
    }
}
