<?php

namespace App\Manager\Client;

use App\Entity\Client\Penalty;
use App\Repository\Client\PenaltyRepository;
use App\Repository\Client\VehicleRepository;
use App\Repository\Client\ClientRepository;
use App\Repository\Client\ContractRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class PenaltyManager
{
    private $em;
    private $penaltyRepository;
    private $vehicleRepository;
    private $clientRepository;
    private $contractRepository;
    private $uploadDir;

    public function __construct(
        EntityManagerInterface $em,
        PenaltyRepository $penaltyRepository,
        VehicleRepository $vehicleRepository,
        ClientRepository $clientRepository,
        ContractRepository $contractRepository,
        KernelInterface $kernel
        )
    {
        $this->em = $em;
        $this->penaltyRepository = $penaltyRepository;
        $this->vehicleRepository = $vehicleRepository;
        $this->clientRepository = $clientRepository;
        $this->contractRepository = $contractRepository;
        $this->uploadDir = $kernel->getProjectDir() . '/public/uploads/compliance/penalties/';
    }

    public function create(object $data, Request $request): Penalty
    {
        $penalty = new Penalty();
        $this->hydrate($penalty, $data);
        $this->handleFiles($penalty, $request);

        $this->em->persist($penalty);
        $this->em->flush();

        return $penalty;
    }

    public function update(string $uuid, object $data, Request $request): Penalty
    {
        $penalty = $this->penaltyRepository->findOneBy(['uuid' => $uuid]);
        if (!$penalty) {
            throw new \Exception("Pénalité introuvable.");
        }

        $this->hydrate($penalty, $data);
        $this->handleFiles($penalty, $request);
        $this->em->flush();

        return $penalty;
    }

    public function delete(Penalty $penalty): Penalty
    {
        $this->em->remove($penalty);
        $this->em->flush();

        return $penalty;
    }

    private function handleFiles(Penalty $penalty, Request $request): void
    {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0775, true);
        }

        $file = $request->files->get('proofFile');
        if ($file && $file->isValid()) {
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $file->move($this->uploadDir, $filename);
            $penalty->setProofUrl('/uploads/compliance/penalties/' . $filename);
        }
    }

    private function hydrate(Penalty $penalty, object $data): void
    {
        if (isset($data->reference))
            $penalty->setReference($data->reference);
        if (isset($data->reason))
            $penalty->setReason($data->reason);
        if (isset($data->amount))
            $penalty->setAmount((float)$data->amount);
        if (isset($data->date))
            $penalty->setDate(new \DateTimeImmutable($data->date));
        if (isset($data->dueDate) && $data->dueDate)
            $penalty->setDueDate(new \DateTimeImmutable($data->dueDate));
        if (isset($data->status))
            $penalty->setStatus($data->status);
        if (isset($data->severity))
            $penalty->setSeverity($data->severity);
        if (isset($data->observation))
            $penalty->setObservation($data->observation);

        if (isset($data->vehicleId)) {
            $vehicle = is_numeric($data->vehicleId)
                ? $this->vehicleRepository->find($data->vehicleId)
                : $this->vehicleRepository->findOneBy(['uuid' => $data->vehicleId]);
            if ($vehicle)
                $penalty->setVehicle($vehicle);
        }

        if (isset($data->clientId)) {
            $client = is_numeric($data->clientId)
                ? $this->clientRepository->find($data->clientId)
                : $this->clientRepository->findOneBy(['uuid' => $data->clientId]);
            if ($client)
                $penalty->setClient($client);
        }

        if (isset($data->contractId)) {
            $contract = is_numeric($data->contractId)
                ? $this->contractRepository->find($data->contractId)
                : $this->contractRepository->findOneBy(['uuid' => $data->contractId]);
            if ($contract)
                $penalty->setContract($contract);
        }
    }
}