<?php

namespace App\Manager\Client;

use App\Entity\Client\Contract;
use App\Entity\Client\Payment;
use App\Entity\Client\PaymentSchedule;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class PaymentScheduleManager
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * Generate a payment schedule for a contract.
     * This will create $installments number of PaymentSchedule entities.
     */
    public function generateSchedule(Contract $contract, float $totalAmount, int $installments, \DateTimeImmutable $startDate, int $ruleDay = 1, bool $excludeSundays = false, string $type = 'Mensualité'): array
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
        $generated = 0;

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
                    if ($excludeSundays && $currentDate->format('N') == 7) {
                        continue; // Skip generating an installment for Sunday
                    }
                }
                else {
                    $currentDate->modify('+1 month');
                }
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
                    if ($excludeSundays && $currentDate->format('N') == 7) {
                        $currentDate->modify('+1 day'); // Push start to Monday
                    }
                }

                // Set the type based on frequency for better labeling
                if ($isWeekly)
                    $type = 'Hebdomadaire';
                if ($isDaily)
                    $type = 'Journalier';
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

            $generated++;
        }

        try {
            $this->em->flush();
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
        // We EXCLUDE Security Deposits (Apport Initial) and Fees from installment coverage
        $payments = $this->em->getRepository(Payment::class)->createQueryBuilder('p')
            ->where('p.contract = :contract')
            ->andWhere('p.status IN (:statuses)')
            ->andWhere('p.type NOT IN (:negatedTypes)')
            ->setParameter('contract', $contract)
            ->setParameter('statuses', ['VALIDÉ', 'VALIDATED', 'Validé'])
            ->setParameter('negatedTypes', ['Apport Initial', 'Frais de dossier'])
            ->orderBy('p.date', 'ASC')
            ->getQuery()
            ->getResult();

        // 3. Re-apply each payment using the water-filling logic
        foreach ($payments as $payment) {
            $coveredSchedules = $this->coverWithPayment($contract, $payment->getAmount(), $payment->getDate());

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
        $this->markOverdueSchedules();
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

    /**
     * Mark all past-due installments ('À venir' or 'Partiel' whose date has passed) as 'En retard'.
     * Should be called periodically (e.g., via a Symfony Command/Scheduler).
     */
    public function markOverdueSchedules(): int
    {
        $today = new \DateTimeImmutable('today');
        $count = 0;

        $overdue = $this->em->getRepository(PaymentSchedule::class)->createQueryBuilder('s')
            ->join('s.contract', 'c')
            ->where('s.expectedDate < :today')
            ->andWhere('s.status IN (:statuses)')
            ->andWhere('c.status NOT IN (:excludedContractStatuses)')
            ->setParameter('today', $today)
            ->setParameter('statuses', ['À venir', 'Partiel'])
            // Exclude suspended, completed or cancelled contracts from automatic late marking
            ->setParameter('excludedContractStatuses', ['SUSPENDU', 'SOLDÉ', 'ANNULÉ', 'RÉSILIÉ'])
            ->getQuery()
            ->getResult();

        foreach ($overdue as $schedule) {
            $schedule->setStatus('En retard');
            $this->em->persist($schedule);
            $count++;
        }

        if ($count > 0) {
            $this->em->flush();
        }

        return $count;
    }

    /**
     * Delete existing schedule for a contract (if recreating)
     */
    public function deleteExistingSchedule(Contract $contract)
    {
        foreach ($contract->getPaymentSchedules() as $schedule) {
            $this->em->remove($schedule);
        }
        $this->em->flush();
    }
}