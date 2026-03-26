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
     * @Route("/", name="index_brand", methods={"GET"})
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
     * @Route("/new", name="new_brand", methods={"POST"})
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
}