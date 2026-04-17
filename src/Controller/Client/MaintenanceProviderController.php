<?php

namespace App\Controller\Client;

use App\Entity\Client\MaintenanceProvider;
use App\Repository\Client\MaintenanceProviderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/maintenance-providers")
 */
class MaintenanceProviderController extends AbstractController
{
    private $entityManager;
    private $repository;

    public function __construct(EntityManagerInterface $entityManager, MaintenanceProviderRepository $repository)
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function index(): JsonResponse
    {
        $providers = $this->repository->findBy([], ['name' => 'ASC']);
        return $this->json($providers, 200, [], ['groups' => ["maintenance"]]);
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

        $provider = new MaintenanceProvider();
        $provider->setName($data['name']);

        $this->entityManager->persist($provider);
        $this->entityManager->flush();

        return $this->json($provider, 201, [], ['groups' => ["maintenance"]]);
    }
}