<?php

namespace App\Controller\Client;

use App\Entity\Client\Brand;
use App\Entity\Client\VehicleModel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route(path="/api/private/vehicle-model")
 */
class VehicleModelController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="index_vehicle_model", methods={"GET"})
     */
    public function index(Request $request)
    {
        $brandId = $request->query->get('brandId');

        $criteria = [];
        if ($brandId) {
            $criteria['brand'] = $brandId;
        }

        $models = $this->entityManager->getRepository(VehicleModel::class)->findBy($criteria);
        // Sort alphabetically
        usort($models, function ($a, $b) {
            return strcmp($a->getName(), $b->getName());
        });

        return $this->json($models, 200, [], ['groups' => ["vehicle_model"]]);
    }

    /**
     * @Route("/new", name="new_vehicle_model", methods={"POST"})
     */
    public function new (Request $request)
    {
        $raw = $request->getContent();
        $data = $raw ? json_decode($raw, true) : $request->request->all();

        if (empty($data['name'])) {
            return $this->json(['message' => 'Le nom du modèle est requis.'], 400);
        }

        if (empty($data['brand'])) {
            return $this->json(['message' => 'La marque est requise pour créer un modèle.'], 400);
        }

        $brand = $this->entityManager->getRepository(Brand::class)->find($data['brand']);
        if (!$brand) {
            return $this->json(['message' => 'Marque introuvable.'], 404);
        }

        // Check unique
        $existing = $this->entityManager->getRepository(VehicleModel::class)->findOneBy([
            'name' => $data['name'],
            'brand' => $brand
        ]);
        if ($existing) {
            return $this->json($existing, 200, [], ['groups' => ["vehicle_model"]]);
        }

        $model = new VehicleModel();
        $model->setName($data['name']);
        $model->setBrand($brand);

        $this->entityManager->persist($model);
        $this->entityManager->flush();

        return $this->json($model, 201, [], ['groups' => ["vehicle_model"]]);
    }
}