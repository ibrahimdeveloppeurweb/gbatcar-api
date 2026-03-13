<?php

namespace App\Manager\Client;

use App\Entity\Client\MaintenanceAlert;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Client\MaintenanceAlertRepository;

class MaintenanceAlertManager
{
    private $em;
    private $maintenanceAlertRepository;

    public function __construct(
        EntityManagerInterface $em,
        MaintenanceAlertRepository $maintenanceAlertRepository
    ) {
        $this->em = $em;
        $this->maintenanceAlertRepository = $maintenanceAlertRepository;
    }

    public function create(object $data): MaintenanceAlert
    {
        // To be implemented
        return new MaintenanceAlert();
    }

    public function update(string $uuid, object $data): MaintenanceAlert
    {
        // To be implemented
        return new MaintenanceAlert();
    }

    public function delete(MaintenanceAlert $maintenanceAlert): MaintenanceAlert
    {
        // To be implemented
        return $maintenanceAlert;
    }
}
