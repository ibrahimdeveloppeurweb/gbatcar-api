<?php

namespace App\Controller\Client;

use App\Entity\Client\Brand;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route(path="/api/private/brand")
 */
class BrandController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="index_brand", methods={"GET"},
     * options={"description"="Liste des marques de véhicule", "permission"="BRAND:LIST"})
     */
    public function index()
    {
        $brands = $this->entityManager->getRepository(Brand::class)->findAll();
        // Sort alphabetically
        usort($brands, function ($a, $b) {
            return strcmp($a->getName(), $b->getName());
        });

        return $this->json($brands, 200, [], ['groups' => ["brand"]]);
    }

    /**
     * @Route("/new", name="new_brand", methods={"POST"},
     * options={"description"="Ajouter une marque de véhicule", "permission"="BRAND:NEW"})
     */
    public function new (Request $request)
    {
        $raw = $request->getContent();
        $data = $raw ? json_decode($raw, true) : $request->request->all();

        if (empty($data['name'])) {
            return $this->json(['message' => 'Le nom de la marque est requis.'], 400);
        }

        // Check unique
        $existing = $this->entityManager->getRepository(Brand::class)->findOneBy(['name' => $data['name']]);
        if ($existing) {
            return $this->json($existing, 200, [], ['groups' => ["brand"]]);
        }

        $brand = new Brand();
        $brand->setName($data['name']);

        $this->entityManager->persist($brand);
        $this->entityManager->flush();

        return $this->json($brand, 201, [], ['groups' => ["brand"]]);
    }

    /**
     * @Route("/{id}/delete", name="delete_brand", methods={"DELETE"},
     * options={"description"="Supprimer une marque de véhicule", "permission"="BRAND:DELETE"})
     */
    public function delete(int $id)
    {
        $brand = $this->entityManager->getRepository(Brand::class)->find($id);

        if (!$brand) {
            return $this->json(['message' => 'Marque introuvable.'], 404);
        }

        // Check if used by any model
        if ($brand->getModels()->count() > 0) {
            return $this->json(['message' => 'Impossible de supprimer cette marque car elle possède des modèles associés.'], 400);
        }

        // Check if used by any vehicle
        $vehicle = $this->entityManager->getRepository(\App\Entity\Client\Vehicle::class)->findOneBy(['brand' => $brand]);
        if ($vehicle) {
            return $this->json(['message' => 'Impossible de supprimer cette marque car elle est déjà associée à un véhicule.'], 400);
        }

        $this->entityManager->remove($brand);
        $this->entityManager->flush();

        return $this->json(['message' => 'Marque supprimée avec succès.']);
    }
}