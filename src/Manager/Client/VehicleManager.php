<?php

namespace App\Manager\Client;

use App\Entity\Client\Vehicle;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Client\VehicleRepository;

class VehicleManager
{
    private $em;
    private $vehicleRepository;

    public function __construct(
        EntityManagerInterface $em,
        VehicleRepository $vehicleRepository
    ) {
        $this->em = $em;
        $this->vehicleRepository = $vehicleRepository;
    }

    public function create(object $data): Vehicle
    {
        // To be implemented
        return new Vehicle();
    }

    public function update(string $uuid, object $data): Vehicle
    {
        // To be implemented
        return new Vehicle();
    }

    public function delete(Vehicle $vehicle): Vehicle
    {
        // To be implemented
        return $vehicle;
    }
}
