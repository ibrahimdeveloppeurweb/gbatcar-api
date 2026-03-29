<?php

namespace App\Controller\Client;

use App\Entity\Client\Contract;
use App\Entity\Client\PaymentSchedule;
use App\Manager\Client\PaymentScheduleManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/api/private/payment-schedule", name="api_payment_schedule_")
 */
class PaymentScheduleController extends AbstractController
{
    private $em;
    private $scheduleManager;
    private $serializer;

    public function __construct(EntityManagerInterface $em, PaymentScheduleManager $scheduleManager, SerializerInterface $serializer)
    {
        $this->em = $em;
        $this->scheduleManager = $scheduleManager;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/generate", name="generate", methods={"POST"},
     * options={"description"="Générer un échéancier de paiement", "permission"="PAYMENT:SCHEDULE:GENERATE"})
     */
    public function generateSchedule(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $contractUuid = $data['contractUuid'] ?? null;
        $totalAmount = $data['totalAmount'] ?? 0;
        $installments = $data['installments'] ?? 0;
        $startDateStr = $data['startDate'] ?? null;
        $ruleDay = $data['ruleDay'] ?? 1;
        $includeSundays = $data['includeSundays'] ?? false;

        if (!$contractUuid || !$startDateStr) {
            return $this->json(['message' => 'Paramètres invalides (contractUuid, startDate requis)'], 400);
        }

        $contract = $this->em->getRepository(Contract::class)->findOneBy(['uuid' => $contractUuid]);
        if (!$contract) {
            return $this->json(['message' => 'Contrat introuvable'], 404);
        }

        $startDate = \DateTimeImmutable::createFromFormat('Y-m-d', $startDateStr);
        if (!$startDate) {
            return $this->json(['message' => 'Format de date invalide. Utilisez YYYY-MM-DD'], 400);
        }

        // Logic: If installments not provided, calculate based on contract duration & frequency
        // Logic: If installments not provided, calculate based on contract duration & frequency
        if ($installments <= 0) {
            $months = $contract->getDurationInMonths() ?: 1;
            $freq = strtolower($contract->getPaymentFrequency() ?: 'monthly');
            $installments = $months; // Default to Monthly

            if (str_contains($freq, 'hebdo') || str_contains($freq, 'weekly')) {
                $installments = $months * 4;
            } elseif (str_contains($freq, 'journalier') || str_contains($freq, 'daily') || str_contains($freq, 'quotidien')) {
                $installments = $months * 30;
            }
        }

        try {
            // SAFETY: deleteExistingSchedule will now throw if payments exist
            if (!$contract->getPaymentSchedules()->isEmpty()) {
                $this->scheduleManager->deleteExistingSchedule($contract);
            }

            $schedules = $this->scheduleManager->generateSchedule($contract, (float) $totalAmount, (int) $installments, $startDate, (int) $ruleDay, (bool) $includeSundays);

            $json = $this->serializer->serialize($schedules, 'json', ['groups' => ['payment_schedule']]);
            return new JsonResponse($json, 201, [], true);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * @Route("/prolong", name="prolong", methods={"POST"},
     * options={"description"="Prolonger un contrat (décaler les échéances)", "permission"="PAYMENT:SCHEDULE:PROLONG"})
     */
    public function prolongSchedule(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $contractUuid = $data['contractUuid'] ?? null;
        $days = (int) ($data['days'] ?? 0);

        if (!$contractUuid || $days <= 0) {
            return $this->json(['message' => 'Paramètres invalides (contractUuid, days > 0 requis)'], 400);
        }

        $contract = $this->em->getRepository(Contract::class)->findOneBy(['uuid' => $contractUuid]);
        if (!$contract) {
            return $this->json(['message' => 'Contrat introuvable'], 404);
        }

        try {
            $count = $this->scheduleManager->shiftScheduleDates($contract, $days);
            return $this->json([
                'message' => sprintf('Échéancier prolongé de %d jours. %d échéances décalées.', $days, $count),
                'shiftedCount' => $count
            ]);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * @Route("/suspend", name="suspend", methods={"POST"},
     * options={"description"="Suspendre ou Réactiver un contrat", "permission"="CONTRACT:SUSPEND"})
     */
    public function toggleSuspension(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $contractUuid = $data['contractUuid'] ?? null;
        $suspend = (bool) ($data['suspend'] ?? true);

        if (!$contractUuid) {
            return $this->json(['message' => 'contractUuid requis'], 400);
        }

        $contract = $this->em->getRepository(Contract::class)->findOneBy(['uuid' => $contractUuid]);
        if (!$contract) {
            return $this->json(['message' => 'Contrat introuvable'], 404);
        }

        if ($suspend) {
            if ($contract->getStatus() === 'SUSPENDU') {
                return $this->json(['message' => 'Le contrat est déjà suspendu'], 400);
            }
            $contract->setStatus('SUSPENDU');
            $message = 'Contrat suspendu avec succès.';
        } else {
            if ($contract->getStatus() !== 'SUSPENDU') {
                return $this->json(['message' => 'Le contrat n\'est pas suspendu'], 400);
            }
            // Logic: toggle back to 'VALIDÉ' (active)
            $contract->setStatus('VALIDÉ');
            $message = 'Contrat réactivé avec succès.';
        }

        $this->em->persist($contract);
        $this->em->flush();

        return $this->json(['message' => $message, 'status' => $contract->getStatus()]);
    }

    /**
     * @Route("/list/{contractUuid}", name="list", methods={"GET"},
     * options={"description"="Lister les échéances d'un contrat", "permission"="PAYMENT:SCHEDULE:LIST"})
     */
    public function getList(string $contractUuid): JsonResponse
    {
        $contract = $this->em->getRepository(Contract::class)->findOneBy(['uuid' => $contractUuid]);
        if (!$contract) {
            return $this->json(['message' => 'Contrat introuvable'], 404);
        }
        // Force refresh of payment coverage to ensure score and statuses are accurate after any change
        $this->scheduleManager->refreshScheduleCoverage($contract);
        
        // Clear EntityManager to force reload from DB in the next findBy, 
        // avoiding stale entities from the Identity Map
        $this->em->clear();
        
        // Re-load contract and schedules after clear
        $contract = $this->em->getRepository(Contract::class)->findOneBy(['uuid' => $contractUuid]);

        $schedules = $this->em->getRepository(PaymentSchedule::class)->findBy(
            ['contract' => $contract],
            ['expectedDate' => 'ASC']
        );

        $json = $this->serializer->serialize($schedules, 'json', ['groups' => ['payment_schedule']]);
        return new JsonResponse($json, 200, [], true);
    }

    /**
     * @Route("/mark-overdue", name="mark_overdue", methods={"POST"},
     * options={"description"="Marquer les échéances en retard", "permission"="PAYMENT:SCHEDULE:MARK:OVERDUE"})
     */
    public function markOverdue(Request $request): JsonResponse
    {
        $contract = null;
        $data = json_decode($request->getContent(), true);
        $contractUuid = $data['contractUuid'] ?? $request->query->get('contractUuid');

        if ($contractUuid) {
            $contract = $this->em->getRepository(Contract::class)->findOneBy(['uuid' => $contractUuid]);
            if (!$contract) {
                return $this->json(['message' => 'Contrat introuvable'], 404);
            }
        }

        $count = $this->scheduleManager->markOverdueSchedules($contract);
        return $this->json([
            'message' => sprintf('%d échéance(s) marquée(s) "En retard".', $count),
            'updated' => $count,
            'scope' => $contract ? 'Contrat ' . $contractUuid : 'Tous les contrats actifs'
        ]);
    }
}