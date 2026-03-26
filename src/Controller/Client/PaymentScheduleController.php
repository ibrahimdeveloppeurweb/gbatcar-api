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
     * @Route("/generate", name="generate", methods={"POST"})
     */
    public function generateSchedule(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $contractUuid = $data['contractUuid'] ?? null;
        $totalAmount = $data['totalAmount'] ?? 0;
        $installments = $data['installments'] ?? 0;
        $startDateStr = $data['startDate'] ?? null;
        $ruleDay = $data['ruleDay'] ?? 1;
        $excludeSundays = $data['excludeSundays'] ?? false;

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
        if ($installments <= 0) {
            $months = $contract->getDurationInMonths() ?: 1;
            $freq = strtolower($contract->getPaymentFrequency() ?: 'monthly');
            $installments = $months; // Default to Monthly

            if (str_contains($freq, 'hebdo') || str_contains($freq, 'weekly')) {
                $installments = $months * 4;
            }
            elseif (str_contains($freq, 'journalier') || str_contains($freq, 'daily') || str_contains($freq, 'quotidien')) {
                $installments = $months * 30;
            }
        }

        try {
            // Optionnel : on supprime l'ancien échéancier si on régénère
            $this->scheduleManager->deleteExistingSchedule($contract);

            $schedules = $this->scheduleManager->generateSchedule($contract, (float)$totalAmount, (int)$installments, $startDate, (int)$ruleDay, (bool)$excludeSundays);

            $json = $this->serializer->serialize($schedules, 'json', ['groups' => ['payment_schedule']]);
            return new JsonResponse($json, 201, [], true);
        }
        catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * @Route("/list/{contractUuid}", name="list", methods={"GET"})
     */
    public function getList(string $contractUuid): JsonResponse
    {
        $contract = $this->em->getRepository(Contract::class)->findOneBy(['uuid' => $contractUuid]);
        if (!$contract) {
            return $this->json(['message' => 'Contrat introuvable'], 404);
        }

        $schedules = $this->em->getRepository(PaymentSchedule::class)->findBy(
        ['contract' => $contract],
        ['expectedDate' => 'ASC']
        );

        $json = $this->serializer->serialize($schedules, 'json', ['groups' => ['payment_schedule']]);
        return new JsonResponse($json, 200, [], true);
    }

    /**
     * @Route("/mark-overdue", name="mark_overdue", methods={"POST"})
     */
    public function markOverdue(): JsonResponse
    {
        $count = $this->scheduleManager->markOverdueSchedules();
        return $this->json([
            'message' => sprintf('%d échéance(s) marquée(s) "En retard".', $count),
            'updated' => $count
        ]);
    }
}