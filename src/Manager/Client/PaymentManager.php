<?php

namespace App\Manager\Client;

use App\Entity\Client\Contract;
use App\Entity\Client\Payment;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Client\PaymentRepository;

class PaymentManager
{
    private $em;
    private $paymentRepository;
    private $security;
    private $scheduleManager;

    public function __construct(
        EntityManagerInterface $em,
        PaymentRepository $paymentRepository,
        \Symfony\Component\Security\Core\Security $security,
        PaymentScheduleManager $scheduleManager
        )
    {
        $this->em = $em;
        $this->paymentRepository = $paymentRepository;
        $this->security = $security;
        $this->scheduleManager = $scheduleManager;
    }

    /**
     * Automatically creates the first payment (Apport Initial / Caution)
     * when a contract is validated.
     */
    public function createInitialDeposit(Contract $contract, ?float $amount = null): Payment
    {
        $amount = $amount ?? ($contract->getCaution() ?: 0);
        $now = new DateTimeImmutable();

        $payment = new Payment();
        $payment->setReference('DEP-' . $now->format('Ymd') . '-' . rand(100, 999));
        $payment->setAmount($amount);
        $payment->setDate($now);
        $payment->setPeriod('Initial');
        $payment->setMethod('Espèces');
        $payment->setType('Apport Initial');
        $payment->setStatus('VALIDÉ');
        $payment->setObservation('Apport initial versé lors de la validation du contrat ' . $contract->getReference());
        $payment->setContract($contract);
        $payment->setClient($contract->getClient());

        // Automatic Attribution to the connected user
        if ($user = $this->security->getUser()) {
            $payment->setRecordedBy($user->getLibelle() ?: $user->getUserIdentifier());
        }
        else {
            $payment->setRecordedBy('Système');
        }

        $this->em->persist($payment);
        $this->em->flush();

        // Update Contract Balance
        //  $this->updateContractBalance($contract);

        return $payment;
    }

    public function createFeePayment(Contract $contract, float $amount): Payment
    {
        $now = new DateTimeImmutable();

        $payment = new Payment();
        $payment->setReference('FEE-' . $now->format('Ymd') . '-' . rand(100, 999));
        $payment->setAmount($amount);
        $payment->setDate($now);
        $payment->setPeriod('Initial');
        $payment->setMethod('Espèces');
        $payment->setType('Frais de dossier');
        $payment->setStatus('VALIDÉ');
        $payment->setObservation('Frais de dossier versés lors de la validation du contrat ' . $contract->getReference());
        $payment->setContract($contract);
        $payment->setClient($contract->getClient());

        // Automatic Attribution to the connected user
        if ($user = $this->security->getUser()) {
            $payment->setRecordedBy($user->getLibelle() ?: $user->getUserIdentifier());
        }
        else {
            $payment->setRecordedBy('Système');
        }

        $this->em->persist($payment);
        $this->em->flush();

        // Update contract balance (fees are excluded but sync is good)
        // $this->updateContractBalance($contract);

        return $payment;
    }

    public function create(object $data, $request = null): Payment
    {
        $payment = new Payment();
        $this->updateFields($payment, $data);

        // Handle Receipt Upload
        if ($request && $request->files->has('receiptFile')) {
            $this->uploadReceipt($payment, $request->files->get('receiptFile'));
        }

        $this->em->persist($payment);
        $this->em->flush();

        // Safety check: if no schedule exists, we should probably warn or block
        // BUT here if we want to BE STRICT (as per user request):
        $count = $this->em->getRepository(\App\Entity\Client\PaymentSchedule::class)->count(['contract' => $payment->getContract()]);
        if ($count === 0 && $payment->getContract()) {
            throw new \LogicException("Impossible d'enregistrer un paiement pour un contrat sans calendrier de paiement.");
        }

        // Auto-cover schedule installments if this is a validated payment
        $status = strtoupper($payment->getStatus() ?? '');
        if (in_array($status, ['VALIDÉ', 'VALIDATED', 'VALIDé']) && $payment->getContract()) {
            if ($payment->getType() === 'PÉNALITÉ') {
                $this->handlePenaltyPayment($payment);
            }
            // Only for standard rent payments
            elseif (!in_array($payment->getType(), ['RÉPARATION_CLIENT', 'FRAIS_AGENCE'])) {
                $this->scheduleManager->refreshScheduleCoverage($payment->getContract());
            }
        }

        return $payment;
    }

    public function update(string $uuid, object $data, $request = null): Payment
    {
        $payment = $this->paymentRepository->findOneByUuid($uuid);
        if (!$payment) {
            throw new \Exception("Paiement introuvable.");
        }

        $oldContract = $payment->getContract();
        $this->updateFields($payment, $data);

        if ($request && $request->files->has('receiptFile')) {
            $this->uploadReceipt($payment, $request->files->get('receiptFile'));
        }

        $this->em->flush();

        // Update balances for both old and new contracts if changed
        if ($payment->getContract()) {
            $this->updateContractBalance($payment->getContract());
            $this->scheduleManager->refreshScheduleCoverage($payment->getContract());
        }
        if ($oldContract && $oldContract !== $payment->getContract()) {
            $this->updateContractBalance($oldContract);
            $this->scheduleManager->refreshScheduleCoverage($oldContract);
        }

        return $payment;
    }

    private function updateFields(Payment $payment, object $data): void
    {
        if (isset($data->contractId)) {
            /** @var \App\Entity\Client\Contract|null $contract */
            $contract = $this->em->getRepository(\App\Entity\Client\Contract::class)->findOneByUuid($data->contractId);
            if ($contract) {
                $payment->setContract($contract);
                $payment->setClient($contract->getClient());
            }
        }

        if (isset($data->amount)) {
            $payment->setAmount((float)$data->amount);
        }
        if (isset($data->date)) {
            $payment->setDate(new \DateTimeImmutable($data->date));
        }
        if (isset($data->period)) {
            $payment->setPeriod($data->period);
        }
        if (isset($data->method)) {
            $payment->setMethod($data->method);
        }

        if (!$payment->getMethod()) {
            $payment->setMethod('Espèces');
        }
        if (isset($data->reference)) {
            $payment->setReference($data->reference);
        }
        if (isset($data->observation)) {
            $payment->setObservation($data->observation);
        }
        if (isset($data->notes)) {
            $payment->setObservation($data->notes);
        }

        // Dynamic Type based on Contract Frequency if not provided
        if (isset($data->type)) {
            $payment->setType($data->type);
        }
        elseif ($contract = $payment->getContract()) {
            $freq = $contract->getPaymentFrequency();
            $type = 'Mensualité'; // Default
            if ($freq === 'Weekly') {
                $type = 'Hebdomadaire';
            }
            if ($freq === 'Daily') {
                $type = 'Journalier';
            }
            $payment->setType($type);
        }

        if (isset($data->status)) {
            $payment->setStatus(strtoupper($data->status));
        }

        if (!$payment->getStatus()) {
            $payment->setStatus('En attente');
        }
        if (!$payment->getType()) {
            $payment->setType('Mensualité');
        }

        // Automatic Attribution to the connected user
        if ($user = $this->security->getUser()) {
            $payment->setRecordedBy($user->getLibelle() ?: $user->getUserIdentifier());
        }
    }

    public function toggleStatus(Payment $payment, string $status): Payment
    {
        $status = Payment::ETAT['VALIDÉ'];
        $payment->setStatus(Payment::ETAT['VALIDÉ']);

        $this->em->persist($payment);
        $this->em->flush(); // Force flush so repository findBy in refreshScheduleCoverage sees the new status

        // Only if successfully validated, we update the payment schedule
        if (in_array($status, ['VALIDÉ', 'VALIDATED']) && $payment->getContract()) {
            if ($payment->getType() === 'PÉNALITÉ') {
                $this->handlePenaltyPayment($payment);
                $this->updateContractBalance($payment->getContract());
            }
            // Only for standard rent payments
            elseif (!in_array($payment->getType(), ['RÉPARATION_CLIENT', 'FRAIS_AGENCE', 'PÉNALITÉ'])) {
                $this->scheduleManager->coverWithPayment($payment->getContract(), $payment->getAmount(), $payment->getDate());
                $this->updateContractBalance($payment->getContract());
            }
            else {
                // For repairs, we still update the contract balance
                $this->updateContractBalance($payment->getContract());
            }
        }

        return $payment;
    }

    private function uploadReceipt(Payment $payment, $file): void
    {
        if (!$file) {
            return;
        }
        $uploadsDir = 'uploads/payments/receipts';
        $extension = $file->getClientOriginalExtension() ?: 'bin';
        $filename = uniqid() . '.' . $extension;
        $file->move($uploadsDir, $filename);
        $payment->setReceiptUrl('/' . $uploadsDir . '/' . $filename);
    }

    private function updateContractBalance(?Contract $contract): void
    {
        if (!$contract) {
            return;
        }

        // Re-calculate total paid from all validated payments
        // We use a query to ensure we get exactly what's in the DB (standardized VALIDÉ status)
        $payments = $this->paymentRepository->createQueryBuilder('p')
            ->where('p.contract = :contract')
            ->andWhere('p.status IN (:statuses)')
            ->andWhere('p.type NOT IN (:excludedTypes)')
            ->setParameter('contract', $contract)
            ->setParameter('statuses', ['VALIDÉ', 'VALIDATED', 'Validé'])
            ->setParameter('excludedTypes', ['RÉPARATION_CLIENT', 'FRAIS_AGENCE', 'PÉNALITÉ', 'Apport Initial', 'Frais de dossier'])
            ->getQuery()
            ->getResult();

        $totalPaid = 0;
        foreach ($payments as $p) {
            $totalPaid += $p->getAmount();
        }

        // Apply the user's rule: Montant Payé = Sum(Valid Payments) - Frais de dossier
        $fees = $contract->getFraisDossier() ?: 0;
        $netPaid = max(0, $totalPaid - $fees);

        // $contract->setPaidAmount($netPaid); // Removed because it's a virtual field mode:AGENT_MODE_EXECUTION

        // Synchronize with Client cached fields
        if ($client = $contract->getClient()) {
            $client->setAmountPaid($totalPaid);
            $client->setTotalAmount($contract->getTotalAmount());
            $client->setCautionAmount($contract->getCaution());
            $client->setPaymentStatus($contract->getPaymentStatus());

            if (method_exists($contract, 'getNextPaymentAmount')) {
                $client->setNextPaymentAmount($contract->getNextPaymentAmount());
            }
            if (method_exists($contract, 'getUnpaidAmount')) {
                $client->setUnpaidAmount($contract->getUnpaidAmount());
            }
        }

        $this->em->flush();
    }

    /**
     * Allocates a payment of type "PÉNALITÉ" to any outstanding penalties for the contract.
     */
    public function handlePenaltyPayment(Payment $payment): void
    {
        $contract = $payment->getContract();
        if (!$contract) {
            return;
        }

        // We use our predefined repository method but with updated and standardized statuses
        $pendingPenalties = $this->em->getRepository(\App\Entity\Client\Penalty::class)->createQueryBuilder('p')
            ->where('p.contract = :contract')
            ->andWhere('p.status IN (:statuses)')
            ->setParameter('contract', $contract)
            ->setParameter('statuses', ['EN ATTENTE', 'IMPAYÉ', 'Non payé', 'Impayé', 'CRITIQUE', 'PARTIAL', 'Partiel'])
            ->orderBy('p.dueDate', 'ASC')
            ->addOrderBy('p.date', 'ASC')
            ->getQuery()
            ->getResult();

        $amountLeft = $payment->getAmount();

        foreach ($pendingPenalties as $penalty) {
            if ($amountLeft <= 0) {
                break;
            }

            $due = $penalty->getAmount() - ($penalty->getPaidAmount() ?: 0);
            if ($due <= 0) {
                continue;
            }

            $pay = min($amountLeft, $due);
            $penalty->setPaidAmount(($penalty->getPaidAmount() ?: 0) + $pay);
            $amountLeft -= $pay;

            if ($penalty->getPaidAmount() >= $penalty->getAmount()) {
                $penalty->setStatus('PAYÉ');
                $penalty->setPaidAt($payment->getDate());
            }
            else {
                $penalty->setStatus('PARTIEL');
            }

            $this->em->persist($penalty);
        }

        $this->em->flush();
    }

    public function delete(Payment $payment): Payment
    {
        $contract = $payment->getContract();
        $this->paymentRepository->remove($payment);

        if ($contract) {
            $this->updateContractBalance($contract);
            $this->scheduleManager->refreshScheduleCoverage($contract);
        }
        return $payment;
    }
}