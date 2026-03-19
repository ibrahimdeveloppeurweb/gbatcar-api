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

    public function __construct(
        EntityManagerInterface $em,
        ContractRepository $contractRepository,
        ClientRepository $clientRepository,
        VehicleRepository $vehicleRepository
        )
    {
        $this->em = $em;
        $this->contractRepository = $contractRepository;
        $this->clientRepository = $clientRepository;
        $this->vehicleRepository = $vehicleRepository;
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

        return $this->save($contract, $data);
    }

    private function save(Contract $contract, object $data): Contract
    {
        if (isset($data->clientId)) {
            $client = $this->clientRepository->findOneBy(['uuid' => $data->clientId]);
            if ($client)
                $contract->setClient($client);
        }

        if (isset($data->vehicleId)) {
            $vehicle = $this->vehicleRepository->findOneBy(['uuid' => $data->vehicleId]);
            if ($vehicle)
                $contract->setVehicle($vehicle);
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

        // Calculations
        $daily = $contract->getDailyRate() ?: 0;
        $months = $contract->getDurationInMonths() ?: 0;
        $caution = $contract->getCaution() ?: 0;

        $total = ($daily * $months) + $caution;
        $contract->setTotalAmount($total);
        // $contract->setProjectedMargin($total * 0.25); // Placeholder formula

        $this->em->persist($contract);
        $this->em->flush();

        return $contract;
    }

    public function delete(Contract $contract): Contract
    {
        $contract->setDeletedAt(new \DateTime());
        $this->em->flush();
        return $contract;
    }
}