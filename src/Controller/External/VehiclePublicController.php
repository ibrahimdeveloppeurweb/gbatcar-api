<?php

namespace App\Controller\External;

use App\Repository\Client\BrandRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VehiclePublicController extends AbstractController
{
    private $brandRepository;

    public function __construct(BrandRepository $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    /**
     * @Route("/api/public/vehicles", name="vehicle_public_list", methods={"GET"})
     */
    public function list(): Response
    {
        // On récupère toutes les marques avec leurs modèles associés
        $brands = $this->brandRepository->findAll();

        return $this->json($brands, 200, [], ['groups' => ['brand']]);
    }
}