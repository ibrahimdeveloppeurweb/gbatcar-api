<?php

namespace App\Manager\Client;

use App\Entity\Client\Maintenance;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Client\MaintenanceRepository;
use App\Repository\Client\VehicleRepository;
use App\Entity\Admin\User;
use App\Entity\Client\MaintenanceType;
use App\Entity\Client\MaintenanceProvider;
use App\Repository\Client\MaintenanceTypeRepository;
use App\Repository\Client\MaintenanceProviderRepository;

class MaintenanceManager
{
    private $em;
    private $maintenanceRepository;
    private $vehicleRepository;
    private $maintenanceTypeRepository;
    private $maintenanceProviderRepository;
    private $clientMailing;

    public function __construct(
        EntityManagerInterface $em,
        MaintenanceRepository $maintenanceRepository,
        VehicleRepository $vehicleRepository,
        MaintenanceTypeRepository $maintenanceTypeRepository,
        MaintenanceProviderRepository $maintenanceProviderRepository,
        \App\Mailing\ClientMailing $clientMailing
        )
    {
        $this->em = $em;
        $this->maintenanceRepository = $maintenanceRepository;
        $this->vehicleRepository = $vehicleRepository;
        $this->maintenanceTypeRepository = $maintenanceTypeRepository;
        $this->maintenanceProviderRepository = $maintenanceProviderRepository;
        $this->clientMailing = $clientMailing;
    }

    public function create(object $data, ?User $user = null): Maintenance
    {
        $maintenance = new Maintenance();
        $maintenance->setReference('MNT-' . strtoupper(substr(uniqid(), -6)));

        if ($user) {
            $maintenance->setCreateBy($user);
        }

        $this->mapDataToEntity($data, $maintenance, $user);

        $this->em->persist($maintenance);
        $this->em->flush();

        // Notification client
        $this->clientMailing->maintenance($maintenance);

        return $maintenance;
    }

    public function update(string $uuid, object $data): Maintenance
    {
        $maintenance = $this->maintenanceRepository->findOneByUuid($uuid);
        if (!$maintenance) {
            throw new \Exception("Intervention introuvable ou supprimée.");
        }

        $this->mapDataToEntity($data, $maintenance, $this->maintenanceRepository->findOneByUuid($uuid)->getCreateBy()); // Approximate user from existing record if possible

        $this->em->flush();

        return $maintenance;
    }

    public function changeStatus(string $uuid, string $status, ?string $date = null): Maintenance
    {
        $maintenance = $this->maintenanceRepository->findOneByUuid($uuid);
        if (!$maintenance) {
            throw new \Exception("Intervention introuvable ou supprimée.");
        }

        $maintenance->setStatus($status);

        $dt = $date ? new \DateTimeImmutable($date) : new \DateTimeImmutable();

        if ($status === 'En cours') {
            $maintenance->setStartDate($dt);
        }
        elseif ($status === 'Terminé' || $status === 'Résolu') {
            $maintenance->setEndDate($dt);
        }

        $this->em->flush();

        return $maintenance;
    }

    public function delete(Maintenance $maintenance): Maintenance
    {
        $this->em->remove($maintenance);
        $this->em->flush();
        return $maintenance;
    }

    private function mapDataToEntity(object $data, Maintenance $maintenance, ?User $user = null): void
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

        if (isset($data->reference))
            $maintenance->setReference($data->reference);
        if (isset($data->type)) {
            $maintenance->setType($data->type);

            // Sync with MaintenanceType entity
            $type = $this->maintenanceTypeRepository->findOneBy(['name' => $data->type]);
            if (!$type) {
                $type = new MaintenanceType();
                $type->setName($data->type);
                if ($user) {
                    $type->setCreateBy($user);
                }
                $this->em->persist($type);
                $this->em->flush();
            }
            $maintenance->setMaintenanceType($type);
        }
        if (isset($data->prestataire))
            $maintenance->setProvider($data->prestataire);
        if (isset($data->provider))
            $maintenance->setProvider($data->provider);

        // Sync with MaintenanceProvider entity
        $providerName = $data->prestataire ?? $data->provider ?? null;
        if ($providerName) {
            $providerEntity = $this->maintenanceProviderRepository->findOneBy(['name' => $providerName]);
            if (!$providerEntity) {
                $providerEntity = new MaintenanceProvider();
                $providerEntity->setName($providerName);
                if ($user) {
                    $providerEntity->setCreateBy($user);
                }
                $this->em->persist($providerEntity);
                $this->em->flush();
            }
        }
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