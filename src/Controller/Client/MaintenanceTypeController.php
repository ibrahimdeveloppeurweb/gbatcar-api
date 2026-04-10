<?php

namespace App\Controller\Client;

use App\Entity\Client\MaintenanceType;
use App\Repository\Client\MaintenanceTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/api/maintenance-types")
 */
class MaintenanceTypeController extends AbstractController
{
    private $entityManager;
    private $repository;

    public function __construct(EntityManagerInterface $entityManager, MaintenanceTypeRepository $repository)
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function index(): JsonResponse
    {
        $types = $this->repository->findBy([], ['name' => 'ASC']);
        return $this->json($types, 200, [], ['groups' => ["maintenance"]]);
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

        $type = new MaintenanceType();
        $type->setName($data['name']);

        $this->entityManager->persist($type);
        $this->entityManager->flush();

        return $this->json($type, 201, [], ['groups' => ["maintenance"]]);
    }
}