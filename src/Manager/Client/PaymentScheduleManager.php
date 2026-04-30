<?php

namespace App\Manager\Client;

use App\Entity\Client\Contract;
use App\Entity\Client\Payment;
use App\Entity\Client\PaymentSchedule;
use App\Repository\Admin\UserRepository;
use App\Services\FirebaseNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class PaymentScheduleManager
{
    private $em;
    private $logger;
    private $clientMailing;
    private $userRepository;
    private $firebaseNotification;

    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger,
        \App\Mailing\ClientMailing $clientMailing,
        UserRepository $userRepository,
        FirebaseNotificationService $firebaseNotification
        )
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->clientMailing = $clientMailing;
        $this->userRepository = $userRepository;
        $this->firebaseNotification = $firebaseNotification;
    }

    /**
     * Generate a payment schedule for a contract.
     * This will create $installments number of PaymentSchedule entities.
     */
    public function generateSchedule(Contract $contract, float $totalAmount, int $installments, \DateTimeImmutable $startDate, int $ruleDay = 1, bool $includeSundays = false, string $type = 'Mensualité'): array
    {
        if ($contract->getStatus() !== 'VALIDÉ') {
            throw new \LogicException("Impossible de générer un échéancier pour un contrat non validé.");
        }

        $schedules = [];

        if ($installments <= 0) {
            throw new \InvalidArgumentException("Le nombre d'échéances doit être supérieur à 0.");
        }

        if ($totalAmount <= 0) {
            throw new \InvalidArgumentException("Le montant à échelonner doit être supérieur à 0.");
        }

        $frequency = strtolower($contract->getPaymentFrequency() ?? 'monthly');
        $isMonthly = str_contains($frequency, 'mensuel') || str_contains($frequency, 'monthly');
        $isWeekly = str_contains($frequency, 'hebdo') || str_contains($frequency, 'weekly');
        $isDaily = str_contains($frequency, 'journalier') || str_contains($frequency, 'quotidien') || str_contains($frequency, 'daily');

        // Base amount per installment
        $baseAmount = floor($totalAmount / $installments);
        // Calculate the remainder to add to the first installment to avoid rounding issues
        $remainder = $totalAmount - ($baseAmount * $installments);

        $currentDate = \DateTime::createFromImmutable($startDate);
        $currentDate->setTime(0, 0, 0);
        $generated = 0;

        file_put_contents('payment_gen_trace.log', "--- Generation Start ---\n");
        file_put_contents('payment_gen_trace.log', "Start Date: " . $startDate->format('Y-m-d') . "\n", FILE_APPEND);
        file_put_contents('payment_gen_trace.log', "Include Sundays: " . ($includeSundays ? 'YES' : 'NO') . "\n", FILE_APPEND);

        while ($generated < $installments) {
            if ($generated > 0) {
                if ($isMonthly) {
                    $currentDate->modify('+1 month');
                    $year = $currentDate->format('Y');
                    $month = $currentDate->format('m');
                    $maxDays = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
                    $targetDay = min($ruleDay, $maxDays);
                    $currentDate->setDate((int)$year, (int)$month, $targetDay);
                }
                elseif ($isWeekly) {
                    $currentDate->modify('+1 week');
                    // Ensure it stays on the ruleDay (1=Mon..7=Sun)
                    $currentDayOfWeek = (int)$currentDate->format('N');
                    $daysDiff = $ruleDay - $currentDayOfWeek;
                    if ($daysDiff !== 0) {
                        $currentDate->modify(($daysDiff > 0 ? '+' : '') . $daysDiff . ' days');
                    }
                }
                elseif ($isDaily) {
                    $currentDate->modify('+1 day');
                    // Si on n'inclut PAS les dimanches et que l'on tombe sur un dimanche (7)
                    if (!$includeSundays && $currentDate->format('N') == 7) {
                        file_put_contents('payment_gen_trace.log', "SKIP (Sunday): " . $currentDate->format('Y-m-d') . "\n", FILE_APPEND);
                        continue; // On saute la génération de cette échéance
                    }
                }
                else {
                    $currentDate->modify('+1 month');
                }
                $currentDate->setTime(0, 0, 0);
            }
            else {
                // First installment alignment
                if ($isMonthly) {
                    $year = $currentDate->format('Y');
                    $month = $currentDate->format('m');
                    $maxDays = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
                    $targetDay = min($ruleDay, $maxDays);
                    $currentDate->setDate((int)$year, (int)$month, $targetDay);
                }
                elseif ($isWeekly) {
                    $currentDayOfWeek = (int)$currentDate->format('N');
                    $daysDiff = $ruleDay - $currentDayOfWeek;
                    if ($daysDiff !== 0) {
                        $currentDate->modify(($daysDiff > 0 ? '+' : '') . $daysDiff . ' days');
                    }
                }
                elseif ($isDaily) {
                    // Si on n'inclut PAS les dimanches et que la date de début est un dimanche
                    if (!$includeSundays && $currentDate->format('N') == 7) {
                        $currentDate->modify('+1 day'); // On décale le début au lundi
                    }
                }

                // Set the type based on frequency for better labeling
                if ($isWeekly)
                    $type = 'Hebdomadaire';
                if ($isDaily)
                    $type = 'Journalier';

                $currentDate->setTime(0, 0, 0);
            }

            $schedule = new PaymentSchedule();
            $schedule->setContract($contract);
            $schedule->setType($type);
            $schedule->setStatus('À venir');
            $schedule->setExpectedDate(\DateTimeImmutable::createFromMutable($currentDate));

            // Add the remainder to the first ACTUAL installment generated
            $amountThisTime = $baseAmount + ($generated === 0 ? $remainder : 0);
            $schedule->setAmount($amountThisTime);

            $this->em->persist($schedule);
            $schedules[] = $schedule;

            // Diagnostic Trace
            $logMsg = sprintf(
                "Gen #%d: %s (N=%s) | Amount: %f\n",
                $generated,
                $currentDate->format('Y-m-d'),
                $currentDate->format('N'),
                $amountThisTime
            );
            file_put_contents('payment_gen_trace.log', $logMsg, FILE_APPEND);

            $generated++;
        }

        try {
            $this->em->flush();

            // Notify client
            $this->clientMailing->paymentScheduleGenerated($contract);

            // Push Notification
            if ($client = $contract->getClient()) {
                $user = $this->userRepository->findOneBy(['username' => $client->getEmail()]);
                if ($user && $user->getFcmToken()) {
                    $this->firebaseNotification->sendNotification($user, 'Nouvel Échéancier 📅', 'Votre planning de paiement a été généré. Vous pouvez le consulter dans l\'application.', ['type' => 'schedule_generated']);
                }
            }
        }
        catch (\Exception $e) {
            $this->logger->error('Error generating payment schedule: ' . $e->getMessage());
            throw $e;
        }

        return $schedules;
    }

    /**
     * Cover installments in chronological order with the incoming payment amount.
     * Acts like filling glasses of water: oldest unpaid first.
     * Called automatically after a new payment is validated.
     * @return PaymentSchedule[] The installments covered by this payment
     */
    public function coverWithPayment(Contract $contract, float $paymentAmount, ?\DateTimeInterface $paidAt = null): array
    {
        $paidAt = $paidAt ?\DateTimeImmutable::createFromInterface($paidAt) : new \DateTimeImmutable();
        $covered = [];
        // Load all pending/overdue installments in chronological order
        $pendingSchedules = $this->em->getRepository(PaymentSchedule::class)->findBy(
        ['contract' => $contract, 'status' => ['À venir', 'En retard', 'Partiel']],
        ['expectedDate' => 'ASC']
        );

        $remaining = $paymentAmount;

        foreach ($pendingSchedules as $schedule) {
            if ($remaining <= 0)
                break;

            $covered[] = $schedule;
            $due = $schedule->getAmount();
            $alreadyPaid = $schedule->getPaidAmount() ?? 0;
            $stillOwed = $due - $alreadyPaid;

            if ($remaining >= $stillOwed) {
                // Fully covers this installment
                $schedule->setStatus('Payé');
                $schedule->setPaidAmount($due);
                $schedule->setPaidAt($paidAt);
                $remaining -= $stillOwed;
            }
            else {
                // Partially covers this installment
                $schedule->setStatus('Partiel');
                $schedule->setPaidAmount($alreadyPaid + $remaining);
                $remaining = 0;
            }

            $this->em->persist($schedule);
        }

        $this->em->flush();
        return $covered;
    }

    /**
     * Completely recalculates the coverage of the schedule for a contract.
     * Useful when a payment is deleted or updated.
     */
    public function refreshScheduleCoverage(Contract $contract): void
    {
        // 1. Reset all schedules for this contract
        $allSchedules = $this->em->getRepository(PaymentSchedule::class)->findBy(['contract' => $contract], ['expectedDate' => 'ASC']);
        foreach ($allSchedules as $schedule) {
            $schedule->setStatus('À venir');
            $schedule->setPaidAmount(0.0);
            $schedule->setPaidAt(null);
            $this->em->persist($schedule);
        }
        // We flush reset first to ensure a clean state
        $this->em->flush();

        // 2. Load all VALIDATED payments for this contract in chronological order
        // We EXCLUDE Security Deposits (Apport Initial), Fees and Penalties from installment coverage
        $payments = $this->em->getRepository(Payment::class)->createQueryBuilder('p')
            ->where('p.contract = :contract')
            ->andWhere('p.status IN (:statuses)')
            ->andWhere('p.type NOT IN (:negatedTypes)')
            ->setParameter('contract', $contract)
            ->setParameter('statuses', ['VALIDÉ', 'VALIDATED', 'Validé'])
            ->setParameter('negatedTypes', ['Apport Initial', 'Frais de dossier', 'RÉPARATION_CLIENT', 'FRAIS_AGENCE', 'PÉNALITÉ'])
            ->orderBy('p.date', 'ASC')
            ->getQuery()
            ->getResult();

        // 3. Re-apply each payment using the water-filling logic
        foreach ($payments as $payment) {
            $amountForSchedule = $payment->getAmount();

            if ($amountForSchedule <= 0) {
                continue;
            }

            $coveredSchedules = $this->coverWithPayment($contract, $amountForSchedule, $payment->getDate());

            // 4. Update the payment's period field based on what it actually covered
            if (!empty($coveredSchedules)) {
                $periods = [];
                foreach ($coveredSchedules as $s) {
                    $periods[] = $this->formatIterationPeriod($s->getExpectedDate(), $contract->getPaymentFrequency());
                }
                $uniquePeriods = array_values(array_unique($periods));

                if (count($uniquePeriods) > 4) {
                    $periodStr = $uniquePeriods[0] . ' -> ' . end($uniquePeriods);
                }
                else {
                    $periodStr = implode(', ', $uniquePeriods);
                }

                $payment->setPeriod($periodStr);
                $this->em->persist($payment);
            }
        }

        $this->em->flush();

        // 5. Finally, re-mark anything unpaid in the past as 'En retard'
        $today = new \DateTimeImmutable('today', new \DateTimeZone('UTC'));
        foreach ($allSchedules as $schedule) {
            // Only mark as overdue if it's still 'À venir' or 'Partiel' and the date is past
            if (in_array($schedule->getStatus(), ['À venir', 'Partiel']) && $schedule->getExpectedDate() < $today) {
                $schedule->setStatus('En retard');
                $this->em->persist($schedule);
            }
        }

        $this->em->flush();
    }

    private function formatIterationPeriod(?\DateTimeImmutable $date, ?string $frequency): string
    {
        if (!$date)
            return 'N/A';

        if ($frequency === 'Daily') {
            return $date->format('d/m/Y');
        }
        elseif ($frequency === 'Weekly') {
            // ISO-8601 week number
            return 'Sem ' . $date->format('W');
        }
        else {
            return $date->format('m/Y');
        }
    }

    public function markOverdueSchedules(Contract $contract = null): int
    {
        if ($contract === null) {
            return $this->markAllOverdueSchedules();
        }

        $today = new \DateTimeImmutable('today'); // Forced to midnight for reliable comparison

        // On ne marque les retards que pour les contrats validés ou en cours
        if (!in_array($contract->getStatus(), ['VALIDÉ', 'ACTIF'])) {
            return 0;
        }

        $qb = $this->em->createQueryBuilder();
        $query = $qb->update(PaymentSchedule::class , 's')
            ->set('s.status', ':overdueStatus')
            ->where('s.expectedDate < :today')
            ->andWhere('s.status IN (:statuses)')
            ->andWhere('s.contract = :contract')
            ->setParameter('overdueStatus', 'En retard')
            ->setParameter('today', $today)
            ->setParameter('statuses', ['À venir', 'Partiel'])
            ->setParameter('contract', $contract)
            ->getQuery();

        return $query->execute();
    }

    /**
     * Mark past-due installments for ALL active contracts.
     * Useful for cron jobs.
     */
    public function markAllOverdueSchedules(): int
    {
        $today = new \DateTimeImmutable('today', new \DateTimeZone('UTC'));

        $qb = $this->em->createQueryBuilder();
        $query = $qb->update(PaymentSchedule::class , 's')
            ->set('s.status', ':overdueStatus')
            ->where('s.expectedDate < :today')
            ->andWhere('s.status IN (:statuses)')
            // Only for active/validated contracts
            ->andWhere('EXISTS (SELECT c FROM App\Entity\Client\Contract c WHERE c = s.contract AND c.status IN (:activeStatuses))')
            ->setParameter('overdueStatus', 'En retard')
            ->setParameter('today', $today)
            ->setParameter('statuses', ['À venir', 'Partiel'])
            ->setParameter('activeStatuses', ['VALIDÉ', 'ACTIF'])
            ->getQuery();

        return $query->execute();
    }

    /**
     * Delete existing schedule for a contract (if recreating)
     * SAFETY: Prevents deletion if any installment is already paid or partially paid.
     */
    public function deleteExistingSchedule(Contract $contract): void
    {
        $schedules = $contract->getPaymentSchedules();

        foreach ($schedules as $schedule) {
            if ($schedule->getStatus() === 'Payé' || $schedule->getStatus() === 'Partiel') {
                throw new \LogicException("Impossible de supprimer l'échéancier : des paiements ont déjà été enregistrés.");
            }
        }

        foreach ($schedules as $schedule) {
            $this->em->remove($schedule);
        }
        $this->em->flush();
    }

    /**
     * Shift all unpaid installments of a contract into the future by a given number of days.
     * Used for prolongation (Force Majeure / Suspension).
     * @return int Number of installments shifted
     */
    public function shiftScheduleDates(Contract $contract, int $days): int
    {
        if ($days <= 0) {
            throw new \InvalidArgumentException("Le nombre de jours de prolongement doit être positif.");
        }

        $count = 0;
        // Shift only 'À venir', 'En retard' and 'Partiel' (for the remaining balance)
        // Note: For 'Partiel', we shift the date, but the paid amount remains recorded.
        $unpaidSchedules = $this->em->getRepository(PaymentSchedule::class)->findBy(
        ['contract' => $contract, 'status' => ['À venir', 'En retard', 'Partiel']],
        ['expectedDate' => 'ASC']
        );

        foreach ($unpaidSchedules as $schedule) {
            $oldDate = $schedule->getExpectedDate();
            $newDate = $oldDate->modify(sprintf('+%d days', $days));

            $schedule->setExpectedDate($newDate);
            // If it was overdue, it might now be 'À venir' depending on the shift
            if ($schedule->getStatus() === 'En retard' && $newDate > new \DateTimeImmutable()) {
                $schedule->setStatus('À venir');
            }

            $this->em->persist($schedule);
            $count++;
        }

        if ($count > 0) {
            // Also shift the contract end date
            $oldEndDate = $contract->getEndDate();
            if ($oldEndDate) {
                $contract->setEndDate($oldEndDate->modify(sprintf('+%d days', $days)));
                $this->em->persist($contract);
            }

            $this->em->flush();

            // Notify client about prolongation
            $this->clientMailing->prolongation($contract, $days);

            // Push Notification
            if ($client = $contract->getClient()) {
                $user = $this->userRepository->findOneBy(['username' => $client->getEmail()]);
                if ($user && $user->getFcmToken()) {
                    $message = sprintf('Votre contrat a été prolongé de %d jours. Vos prochaines échéances ont été décalées.', $days);
                    $this->firebaseNotification->sendNotification($user, 'Contrat Prolongé ⏳', $message, ['type' => 'schedule_shifted']);
                }
            }
        }

        return $count;
    }
}