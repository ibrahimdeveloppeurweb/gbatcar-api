<?php

namespace App\Controller\Client;

use App\Entity\Client\ContractDuration;
use App\Repository\Client\ContractDurationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/contract-durations")
 */
class ContractDurationController extends AbstractController
{
    private $entityManager;
    private $repository;

    public function __construct(EntityManagerInterface $entityManager, ContractDurationRepository $repository)
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function index(): JsonResponse
    {
        $durations = $this->repository->findBy([], ['monthsCount' => 'ASC']);
        return $this->json($durations, 200, [], ['groups' => ["duration"]]);
    }

    /**
     * @Route("", methods={"POST"})
     */
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['name'])) {
            return $this->json(['error' => 'Name is required'], 400);
        }

        // Extract numeric value from name if monthsCount not provided
        $monthsCount = $data['monthsCount'] ?? null;
        if ($monthsCount === null) {
            preg_match('/\d+/', $data['name'], $matches);
            $monthsCount = isset($matches[0]) ? (int)$matches[0] : 0;
        }

        $duration = new ContractDuration();
        $duration->setName($data['name']);
        $duration->setMonthsCount($monthsCount);

        if ($this->getUser()) {
            $duration->setCreateBy($this->getUser());
        }

        $this->entityManager->persist($duration);
        $this->entityManager->flush();

        return $this->json($duration, 201, [], ['groups' => ["duration"]]);
    }
}