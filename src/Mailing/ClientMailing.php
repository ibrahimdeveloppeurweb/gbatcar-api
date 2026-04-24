<?php

namespace App\Mailing;

use App\Entity\Client\Client;
use App\Entity\Client\Contract;
use App\Entity\Client\Payment;
use App\Entity\Client\Penalty;
use App\Entity\Client\Maintenance;
use App\Entity\Client\PaymentSchedule;
use App\Services\MailService;

class ClientMailing
{
    private $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    /**
     * Bienvenue lors de l'enregistrement du client
     * 
     * @param Client $client
     * @return void
     */
    public function clientRegistered(Client $client)
    {
        if ($client->getEmail()) {
            $this->mailService->email(
                $client->getEmail(),
            [
                'client' => $client
            ],
                'Bienvenue chez GBATCAR !',
                3,
                'mail/client/client/_client_registered.html.twig'
            );
        }
    }

    /**
     * Notification à la création du contrat
     * 
     * @param Contract $contract
     * @param array|null $credentials
     * @return void
     */
    public function contract(Contract $contract, ?array $credentials = null)
    {
        $client = $contract->getClient();
        if ($client && $client->getEmail()) {
            $this->mailService->email(
                $client->getEmail(),
            [
                'contract' => $contract,
                'client' => $client,
                'vehicle' => $contract->getVehicle(),
                'credentials' => $credentials
            ],
                'GBATCAR - Création de votre contrat de location vente ' . $contract->getReference(),
                3,
                'mail/client/client/_contrat.html.twig'
            );
        }
    }

    /**
     * Notification lors d'un paiement
     * 
     * @param Payment $payment
     * @return void
     */
    public function payment(Payment $payment)
    {
        $client = $payment->getClient() ?? ($payment->getContract() ? $payment->getContract()->getClient() : null);
        if ($client && $client->getEmail()) {
            $attachments = [];

            // Resolve absolute path for receipt if it exists
            if ($payment->getReceiptUrl()) {
                // Assuming public/ is the root for uploads
                $receiptPath = '/Users/mac/Documents/GBATCAR/gbatcar-api/public' . $payment->getReceiptUrl();
                if (file_exists($receiptPath)) {
                    $attachments[] = $receiptPath;
                }
            }

            $this->mailService->email(
                $client->getEmail(),
            [
                'payment' => $payment,
                'client' => $client,
                'contract' => $payment->getContract()
            ],
                'GBATCAR - Reçu de paiement #' . $payment->getReference(),
                3,
                'mail/client/client/_paiement.html.twig',
                $attachments
            );
        }
    }

    /**
     * Notification lors d'une pénalité
     * 
     * @param Penalty $penalty
     * @return void
     */
    public function penalty(Penalty $penalty)
    {
        $client = $penalty->getClient() ?? ($penalty->getContract() ? $penalty->getContract()->getClient() : null);
        if ($client && $client->getEmail()) {
            $this->mailService->email(
                $client->getEmail(),
            [
                'penalty' => $penalty,
                'client' => $client,
                'contract' => $penalty->getContract()
            ],
                'GBATCAR - Notification de pénalité ' . $penalty->getReference(),
                3,
                'mail/client/client/_penalty.html.twig'
            );
        }
    }

    /**
     * Notification pour une maintenance (prévisionnelle ou effectuée)
     * 
     * @param Maintenance $maintenance
     * @return void
     */
    public function maintenance(Maintenance $maintenance)
    {
        $vehicle = $maintenance->getVehicle();
        $contract = $maintenance->getContract();
        $client = $maintenance->getClient() ?? ($contract ? $contract->getClient() : null);

        if ($client && $client->getEmail()) {
            $this->mailService->email(
                $client->getEmail(),
            [
                'maintenance' => $maintenance,
                'vehicle' => $vehicle,
                'client' => $client
            ],
                'GBATCAR - Suivi de maintenance de votre véhicule',
                3,
                'mail/client/client/_maintenance.html.twig'
            );
        }
    }

    /**
     * Notification lors de la résiliation du contrat
     * 
     * @param Contract $contract
     * @return void
     */
    public function termination(Contract $contract)
    {
        $client = $contract->getClient();
        if ($client && $client->getEmail()) {
            $this->mailService->email(
                $client->getEmail(),
            [
                'contract' => $contract,
                'client' => $client
            ],
                'Félicitations - Fin de votre contrat GBATCAR ' . $contract->getReference(),
                3,
                'mail/client/client/_termination.html.twig'
            );
        }
    }

    /**
     * Notification lors de la rupture (échec) du contrat
     * 
     * @param Contract $contract
     * @return void
     */
    public function rupture(Contract $contract)
    {
        $client = $contract->getClient();
        if ($client && $client->getEmail()) {
            $this->mailService->email(
                $client->getEmail(),
            [
                'contract' => $contract,
                'client' => $client
            ],
                'GBATCAR - Notification de rupture de votre contrat ' . $contract->getReference(),
                3,
                'mail/client/client/_rupture.html.twig'
            );
        }
    }

    /**
     * Rappel de paiement (échéance proche)
     * 
     * @param PaymentSchedule $schedule
     * @return void
     */
    public function reminder(PaymentSchedule $schedule)
    {
        $contract = $schedule->getContract();
        $client = $contract ? $contract->getClient() : null;
        if ($client && $client->getEmail()) {
            $this->mailService->email(
                $client->getEmail(),
            [
                'schedule' => $schedule,
                'contract' => $contract,
                'client' => $client
            ],
                'GBATCAR - Rappel : Échéance de paiement proche',
                3,
                'mail/client/client/_reminder.html.twig'
            );
        }
    }

    /**
     * Notification de paiement impayé
     * 
     * @param PaymentSchedule $schedule
     * @return void
     */
    public function unpaid(PaymentSchedule $schedule)
    {
        $contract = $schedule->getContract();
        $client = $contract ? $contract->getClient() : null;
        if ($client && $client->getEmail()) {
            $this->mailService->email(
                $client->getEmail(),
            [
                'schedule' => $schedule,
                'contract' => $contract,
                'client' => $client
            ],
                'GBATCAR - Alerte : Paiement impayé',
                1, // Haute priorité
                'mail/client/client/_unpaid.html.twig'
            );
        }
    }

    /**
     * Notification lors de la génération de l'échéancier complet
     * 
     * @param Contract $contract
     * @return void
     */
    public function paymentScheduleGenerated(Contract $contract)
    {
        $client = $contract->getClient();
        if ($client && $client->getEmail()) {
            $schedules = $contract->getPaymentSchedules();

            $this->mailService->email(
                $client->getEmail(),
            [
                'contract' => $contract,
                'client' => $client,
                'schedules' => $schedules,
                'vehicle' => $contract->getVehicle()
            ],
                'GBATCAR - Votre calendrier de paiement - ' . $contract->getReference(),
                3,
                'mail/client/client/_payment_schedule.html.twig'
            );
        }
    }

    /**
     * Notification lors du prolongement du contrat (Force Majeure)
     * 
     * @param Contract $contract
     * @param int $days
     * @return void
     */
    public function prolongation(Contract $contract, int $days)
    {
        $client = $contract->getClient();
        if ($client && $client->getEmail()) {
            $lastScheduleDate = $contract->getEndDate();
            $schedules = $contract->getPaymentSchedules();

            if (count($schedules) > 0) {
                $maxDate = null;
                foreach ($schedules as $s) {
                    if ($maxDate === null || $s->getExpectedDate() > $maxDate) {
                        $maxDate = $s->getExpectedDate();
                    }
                }
                if ($maxDate) {
                    $lastScheduleDate = $maxDate;
                }
            }

            $this->mailService->email(
                $client->getEmail(),
            [
                'contract' => $contract,
                'client' => $client,
                'days' => $days,
                'lastInstallmentDate' => $lastScheduleDate,
                'vehicle' => $contract->getVehicle()
            ],
                'GBATCAR - Prolongation de votre contrat - ' . $contract->getReference(),
                3,
                'mail/client/client/_prolongation.html.twig'
            );
        }
    }
}