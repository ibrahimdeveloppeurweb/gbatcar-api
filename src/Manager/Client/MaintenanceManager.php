<?php

namespace App\Manager\Client;

use App\Entity\Client\Maintenance;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Client\MaintenanceRepository;
use App\Repository\Client\VehicleRepository;
use App\Entity\Admin\User;

class MaintenanceManager
{
    private $em;
    private $maintenanceRepository;
    private $vehicleRepository;

    public function __construct(
        EntityManagerInterface $em,
        MaintenanceRepository $maintenanceRepository,
        VehicleRepository $vehicleRepository
        )
    {
        $this->em = $em;
        $this->maintenanceRepository = $maintenanceRepository;
        $this->vehicleRepository = $vehicleRepository;
    }

    public function create(object $data, ?User $user = null): Maintenance
    {
        $maintenance = new Maintenance();
        $maintenance->setReference('MNT-' . strtoupper(substr(uniqid(), -6)));

        if ($user) {
            $maintenance->setCreateBy($user);
        }

        $this->mapDataToEntity($data, $maintenance);

        $this->em->persist($maintenance);
        $this->em->flush();

        return $maintenance;
    }

    public function update(string $uuid, object $data): Maintenance
    {
        $maintenance = $this->maintenanceRepository->findOneByUuid($uuid);
        if (!$maintenance) {
            throw new \Exception("Intervention introuvable ou supprimée.");
        }

        $this->mapDataToEntity($data, $maintenance);

        $this->em->flush();

        return $maintenance;
    }

    public function changeStatus(string $uuid, string $status): Maintenance
    {
        $maintenance = $this->maintenanceRepository->findOneByUuid($uuid);
        if (!$maintenance) {
            throw new \Exception("Intervention introuvable ou supprimée.");
        }

        $maintenance->setStatus($status);
        $this->em->flush();

        return $maintenance;
    }

    public function delete(Maintenance $maintenance): Maintenance
    {
        $this->em->remove($maintenance);
        $this->em->flush();
        return $maintenance;
    }

    private function mapDataToEntity(object $data, Maintenance $maintenance): void
    {
        if (isset($data->vehicle)) {
            $vehicle = $this->vehicleRepository->findOneByUuid($data->vehicle);
            if ($vehicle) {
                $maintenance->setVehicle($vehicle);
                $maintenance->setClient($vehicle->getClient());
            }
            else {
                throw new \Exception("Le véhicule sélectionné est introuvable.");
            }
        }

        if (isset($data->type))
            $maintenance->setType($data->type);
        if (isset($data->prestataire))
            $maintenance->setProvider($data->prestataire);
        if (isset($data->provider))
            $maintenance->setProvider($data->provider);
        if (isset($data->cost))
            $maintenance->setCost((float)$data->cost);
        if (isset($data->statut))
            $maintenance->setStatus($data->statut);
        if (isset($data->status))
            $maintenance->setStatus($data->status);
        if (isset($data->kilometrage))
            $maintenance->setKilometrage((float)$data->kilometrage);
        if (isset($data->technicien))
            $maintenance->setTechnician($data->technicien);
        if (isset($data->observation))
            $maintenance->setObservation($data->observation);
        if (isset($data->description))
            $maintenance->setDescription($data->description);

        if (!empty($data->dateIntervention)) {
            $maintenance->setDateIntervention(new \DateTimeImmutable($data->dateIntervention));
        }

        if (!empty($data->nextMaintenanceDate)) {
            $maintenance->setNextMaintenanceDate(new \DateTimeImmutable($data->nextMaintenanceDate));
        }

        if (isset($data->nextMaintenanceMileage) && $data->nextMaintenanceMileage !== "") {
            $maintenance->setNextMaintenanceMileage((int)$data->nextMaintenanceMileage);
        }
    }
}