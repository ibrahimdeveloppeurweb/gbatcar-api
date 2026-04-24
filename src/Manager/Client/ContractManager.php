<?php

namespace App\Manager\Client;

use App\Entity\Client\Contract;
use App\Repository\Client\ClientRepository;
use App\Repository\Client\ContractRepository;
use App\Repository\Client\VehicleRepository;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

class ContractManager
{
    private $em;
    private $contractRepository;
    private $clientRepository;
    private $vehicleRepository;
    private $paymentManager;
    private $clientMailing;
    private $userManager;

    public function __construct(
        EntityManagerInterface $em,
        ContractRepository $contractRepository,
        ClientRepository $clientRepository,
        VehicleRepository $vehicleRepository,
        PaymentManager $paymentManager,
        \App\Mailing\ClientMailing $clientMailing,
        \App\Manager\Admin\UserManager $userManager
        )
    {
        $this->em = $em;
        $this->contractRepository = $contractRepository;
        $this->clientRepository = $clientRepository;
        $this->vehicleRepository = $vehicleRepository;
        $this->paymentManager = $paymentManager;
        $this->clientMailing = $clientMailing;
        $this->userManager = $userManager;
    }

    public function create(object $data): Contract
    {
        $contract = new Contract();

        // Reference generation (simple for now)
        $contract->setReference('CTR-' . date('Ymd') . '-' . rand(100, 999));
        $contract->setStatus('NEW');
        $contract->setPaymentStatus('PENDING');

        return $this->save($contract, $data);
    }

    public function update(string $uuid, object $data): Contract
    {
        $contract = $this->contractRepository->findOneBy(['uuid' => $uuid]);
        if (!$contract) {
            throw new \Exception("Contract not found");
        }

        if (in_array(strtoupper($contract->getStatus()), ['VALIDÉ', 'TERMINÉ', 'ROMPU', 'SOLDÉ', 'RÉSILIÉ'])) {
            throw new \Exception("Impossible de modifier un contrat dont le statut est " . $contract->getStatus());
        }

        return $this->save($contract, $data);
    }

    private function save(Contract $contract, object $data): Contract
    {
        if (isset($data->clientId)) {
            $client = $this->clientRepository->findOneBy(['uuid' => $data->clientId]);
            if ($client)
                $contract->setClient($client);
        }

        if (isset($data->vehicleId) && $data->vehicleId) {
            $vehicle = $this->vehicleRepository->findOneBy(['uuid' => $data->vehicleId]);
            if ($vehicle) {
                $contract->setVehicle($vehicle);
                if ($contract->getClient()) {
                    $vehicle->setClient($contract->getClient());
                }
                // Important: as soon as a vehicle is assigned to a contract (even NEW), it is no longer available.
                $vehicle->setStatut('En Location-Vente');
            }
        }
        else {
            // Nullable vehicle
            $contract->setVehicle(null);
        }


        // Handle multiple vehicle demands
        if (isset($data->vehicleDemands) && is_array($data->vehicleDemands)) {
            // Clear existing demands and free vehicles
            foreach ($contract->getVehicleDemands() as $oldDemand) {
                foreach ($oldDemand->getAssignedVehicles() as $oldV) {
                    $oldV->setStatut('Disponible');
                    $oldV->setClient(null);
                }
                $this->em->remove($oldDemand);
            }
            $contract->getVehicleDemands()->clear();

            foreach ($data->vehicleDemands as $demandArray) {
                // Decode from array or object
                $demandData = (object)$demandArray;

                $demand = new \App\Entity\Client\ContractVehicleDemand();

                if (isset($demandData->brandId) && $demandData->brandId) {
                    $brand = $this->em->getRepository(\App\Entity\Client\Brand::class)->find($demandData->brandId);
                    if ($brand)
                        $demand->setBrand($brand);
                }

                if (isset($demandData->modelId) && $demandData->modelId) {
                    $model = $this->em->getRepository(\App\Entity\Client\VehicleModel::class)->find($demandData->modelId);
                    if ($model)
                        $demand->setVehicleModel($model);
                }

                if (isset($demandData->quantity)) {
                    $demand->setQuantity((int)$demandData->quantity);
                }
                else {
                    $demand->setQuantity(1);
                }

                // Associate specific physical vehicles if provided
                if (isset($demandData->assignedVehicleIds) && is_array($demandData->assignedVehicleIds)) {
                    foreach ($demandData->assignedVehicleIds as $vId) {
                        $vehicle = $this->vehicleRepository->findOneBy(['uuid' => $vId]);
                        if ($vehicle) {
                            $demand->addAssignedVehicle($vehicle);
                            $vehicle->setStatut('En Location-Vente');
                            if ($contract->getClient()) {
                                $vehicle->setClient($contract->getClient());
                            }
                        }
                    }
                }

                $contract->addVehicleDemand($demand);
                $this->em->persist($demand);
            }
        }

        if (isset($data->usageType))
            $contract->setUsageType($data->usageType);
        if (isset($data->paymentFrequency))
            $contract->setPaymentFrequency($data->paymentFrequency);
        if (isset($data->dailyRate))
            $contract->setDailyRate((float)$data->dailyRate);
        if (isset($data->duration))
            $contract->setDurationInMonths((int)$data->duration);
        if (isset($data->cautionAmount))
            $contract->setCaution((float)$data->cautionAmount);
        if (isset($data->insuranceSplit))
            $contract->setMaintenanceAndInsurance($data->insuranceSplit);
        if (isset($data->gracePeriod))
            $contract->setGracePeriodDays((int)$data->gracePeriod);
        if (isset($data->penaltyRate))
            $contract->setPenaltyRate((float)$data->penaltyRate);
        if (isset($data->notes))
            $contract->setObservation($data->notes);
        if (isset($data->prixDeVente))
            $contract->setPrixDeVente((float)$data->prixDeVente);

        // Checklist
        if (isset($data->hasValidID))
            $contract->setHasValidID((bool)$data->hasValidID);
        if (isset($data->hasDriverLicense))
            $contract->setHasDriverLicense((bool)$data->hasDriverLicense);
        if (isset($data->hasProofOfAddress))
            $contract->setHasProofOfAddress((bool)$data->hasProofOfAddress);
        if (isset($data->hasCriminalRecord))
            $contract->setHasCriminalRecord((bool)$data->hasCriminalRecord);

        // Dates
        if (isset($data->startDate)) {
            $startDate = new DateTimeImmutable($data->startDate);
            $contract->setStartDate($startDate);

            // Auto-calculate end date
            $months = $contract->getDurationInMonths() ?: 0;
            $contract->setEndDate($startDate->modify("+$months month"));
        }

        if (isset($data->fraisDossier))
            $contract->setFraisDossier((float)$data->fraisDossier);

        // Calculations — use correct number of periods based on payment frequency
        $daily = $contract->getDailyRate() ?: 0;
        $months = $contract->getDurationInMonths() ?: 0;
        $caution = $contract->getCaution() ?: 0;
        $fees = $contract->getFraisDossier() ?: 0;
        $frequency = $contract->getPaymentFrequency() ?: 'Monthly';

        if ($frequency === 'Monthly') {
            $numberOfPeriods = $months;
        }
        elseif ($frequency === 'Weekly') {
            $numberOfPeriods = $months * 4;
        }
        elseif ($frequency === 'Daily') {
            $numberOfPeriods = $months * 30;
        }
        else {
            $numberOfPeriods = $months;
        }

        // Total Exigible = Rent * Periods + Full Caution
        $total = ($daily * $numberOfPeriods) + $caution;
        $contract->setTotalAmount($total);
        // $contract->setProjectedMargin($total * 0.25); // Placeholder formula

        $this->em->persist($contract);
        $this->em->flush();

        return $contract;
    }

    public function validate(Contract $contract): Contract
    {
        $contract->setStatus('VALIDÉ');
        $contract->setPaymentStatus('À jour');

        $now = new DateTimeImmutable();
        $contract->setStartDate($now);

        // Recalculate end date based on duration
        $months = $contract->getDurationInMonths() ?: 0;
        $contract->setEndDate($now->modify("+$months month"));

        // Update Client Status
        if ($client = $contract->getClient()) {
            $client->setStatus('Dossier Approuvé');
        }
        // Update Vehicle Status and Link to Client
        if ($vehicle = $contract->getVehicle()) {
            $vehicle->setStatut('En Location-Vente');
            if ($client = $contract->getClient()) {
                $vehicle->setClient($client);
            }
            $this->em->persist($vehicle);
        }

        // Auto-create the initial deposit payment (Apport Initial) and File Fees
        $caution = $contract->getCaution() ?: 0;
        $fees = $contract->getFraisDossier() ?: 0;
        $actualCaution = $caution - $fees;

        if ($actualCaution > 0) {
            $this->paymentManager->createInitialDeposit($contract, $actualCaution);
        }
        if ($fees > 0) {
            $this->paymentManager->createFeePayment($contract, $fees);
        }

        $this->em->flush();

        // Auto-create client account
        $credentials = null;
        if ($client = $contract->getClient()) {
            $credentials = $this->userManager->createClientAccount($client);
        }

        // Notification client
        $this->clientMailing->contract($contract, $credentials);

        return $contract;
    }

    public function terminate(Contract $contract): Contract
    {
        $contract->setStatus('TERMINÉ');
        $contract->setTerminatedAt(new \DateTimeImmutable());

        // Normal end of Location-Vente: Vehicle becomes client property
        if ($vehicle = $contract->getVehicle()) {
            $vehicle->setStatut('Vendu');
            // We KEEP the client on the vehicle as he is now the owner
            $this->em->persist($vehicle);
        }

        // Free fleet vehicles (if any)
        foreach ($contract->getVehicleDemands() as $demand) {
            foreach ($demand->getAssignedVehicles() as $v) {
                $v->setStatut('Vendu');
                $this->em->persist($v);
            }
        }

        $this->em->flush();

        // Notification client
        $this->clientMailing->termination($contract);

        return $contract;
    }

    public function rupture(Contract $contract): Contract
    {
        $contract->setStatus('ROMPU');
        $contract->setTerminatedAt(new \DateTimeImmutable());

        // Breach of contract: Vehicle returns to company stock
        if ($vehicle = $contract->getVehicle()) {
            $vehicle->setStatut('Disponible');
            $vehicle->setClient(null); // Back to inventory
            $this->em->persist($vehicle);
        }

        // Return fleet vehicles to inventory
        foreach ($contract->getVehicleDemands() as $demand) {
            foreach ($demand->getAssignedVehicles() as $v) {
                $v->setStatut('Disponible');
                $v->setClient(null);
                $this->em->persist($v);
            }
        }

        $this->em->flush();

        // Notification client
        $this->clientMailing->rupture($contract);

        return $contract;
    }

    public function delete(Contract $contract): Contract
    {
        if (in_array(strtoupper($contract->getStatus()), ['VALIDÉ', 'TERMINÉ', 'ROMPU', 'SOLDÉ', 'RÉSILIÉ'])) {
            throw new \Exception("Impossible de supprimer un contrat dont le statut est " . $contract->getStatus());
        }

        // Release main vehicle
        if ($vehicle = $contract->getVehicle()) {
            $vehicle->setStatut('Disponible');
            $vehicle->setClient(null);
            $this->em->persist($vehicle);
        }

        // Release fleet vehicles (from demands) and remove the demands
        foreach ($contract->getVehicleDemands() as $demand) {
            foreach ($demand->getAssignedVehicles() as $v) {
                $v->setStatut('Disponible');
                $v->setClient(null);
                $this->em->persist($v);
            }
            $this->em->remove($demand);
        }

        $contract->setDeletedAt(new \DateTime());
        $this->em->flush();
        return $contract;
    }
}