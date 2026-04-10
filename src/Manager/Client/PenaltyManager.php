<?php

namespace App\Manager\Client;

use App\Entity\Client\Penalty;
use App\Entity\Client\Contract;
use App\Entity\Client\PaymentSchedule;
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

    public function calculatePenaltiesForContract(Contract $contract, bool $flush = true): array
    {
        $today = new \DateTimeImmutable('today', new \DateTimeZone('UTC'));
        $penaltyRate = $contract->getPenaltyRate() ?? 0;
        $graceDays = $contract->getGracePeriodDays() ?? 0;
        $penaltiesCreated = [];

        if ($penaltyRate <= 0 || !in_array($contract->getStatus(), ['VALIDÉ', 'ACTIVE', 'En cours'])) {
            return [];
        }

        $overdueSchedules = $this->em->getRepository(PaymentSchedule::class)->findBy([
            'contract' => $contract,
            'status' => ['En retard', 'Partiel']
        ]);

        foreach ($overdueSchedules as $schedule) {
            $dueDate = $schedule->getExpectedDate();
            $limitDate = $dueDate->modify(sprintf('+%d days', $graceDays));

            if ($today > $limitDate) {
                // Calculate days of delay
                $diff = $today->diff($limitDate);
                $daysDelay = $diff->days;

                if ($daysDelay > 0) {
                    $installmentAmount = $schedule->getAmount();
                    $dailyPenalty = ($installmentAmount * $penaltyRate) / 100;
                    $totalPenaltyAmount = $dailyPenalty * $daysDelay;

                    // Find existing penalty for this schedule
                    $penalty = $this->penaltyRepository->findOneBy([
                        'contract' => $contract,
                        'paymentSchedule' => $schedule
                    ]);

                    if (!$penalty) {
                        $penalty = new Penalty();
                        $penalty->setContract($contract);
                        $penalty->setClient($contract->getClient());
                        $penalty->setVehicle($contract->getVehicle());
                        $penalty->setPaymentSchedule($schedule);
                        $penalty->setReference('PEN-' . strtoupper(substr($contract->getUuid(), 0, 5)) . '-' . $schedule->getId());
                        $penalty->setDate($today);
                        $penalty->setStatus('EN ATTENTE');
                        $penalty->setSeverity('Moyenne');
                        $this->em->persist($penalty);
                    }

                    $penalty->setAmount($totalPenaltyAmount);
                    $penalty->setReason(sprintf("Pénalité de retard journalière (%d jours de retard après délai de grâce)", $daysDelay));
                    $penaltiesCreated[] = $penalty;
                }
            }
        }

        if ($flush && !empty($penaltiesCreated)) {
            $this->em->flush();
        }

        return $penaltiesCreated;
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
        // Default values for new penalties
        if (!$penalty->getId()) {
            if (!isset($data->status)) {
                $penalty->setStatus('EN ATTENTE');
            }
            if (!isset($data->date)) {
                $penalty->setDate(new \DateTimeImmutable());
            }
            if (!isset($data->severity)) {
                $penalty->setSeverity('Moyenne');
            }
            if (!isset($data->reference)) {
                $datePart = (new \DateTime())->format('Ymd');
                $randomPart = strtoupper(substr(uniqid(), -4));
                $penalty->setReference('PEN-MAN-' . $datePart . '-' . $randomPart);
            }
        }

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
            if ($contract) {
                $penalty->setContract($contract);
                if (!$penalty->getClient()) {
                    $penalty->setClient($contract->getClient());
                }
                if (!$penalty->getVehicle()) {
                    $penalty->setVehicle($contract->getVehicle());
                }
            }
        }
        elseif ($penalty->getVehicle()) {
            $activeContract = $penalty->getVehicle()->getActiveContract();
            if ($activeContract) {
                $penalty->setContract($activeContract);
                if (!$penalty->getClient()) {
                    $penalty->setClient($activeContract->getClient());
                }
            }
        }
    }

    public function updateStatuses(): int
    {
        $today = new \DateTimeImmutable('today', new \DateTimeZone('UTC'));
        $count = 0;

        // Penalties that should become IMPAYÉ or CRITIQUE
        $penalties = $this->penaltyRepository->findBy([
            'status' => ['EN ATTENTE', 'IMPAYÉ', 'Non payé', 'Impayé']
        ]);

        foreach ($penalties as $penalty) {
            $dueDate = $penalty->getDueDate();
            if (!$dueDate)
                continue;

            $oldStatus = $penalty->getStatus();
            $newStatus = $oldStatus;

            // Rule 1: Past due date -> IMPAYÉ
            if ($today > $dueDate) {
                $newStatus = 'Impayé';
            }

            // Rule 2: Overdue by > 3 days OR Severity is Élevée -> CRITIQUE
            $dueDatePlus3 = $dueDate->modify('+3 days');
            if (($today > $dueDatePlus3) || $penalty->getSeverity() === 'Élevée') {
                if ($today > $dueDate) { // Must at least be past due date
                    $newStatus = 'CRITIQUE';
                }
            }

            if ($newStatus !== $oldStatus) {
                $penalty->setStatus($newStatus);
                $this->em->persist($penalty);
                $count++;
            }
        }

        if ($count > 0) {
            $this->em->flush();
        }

        return $count;
    }
}