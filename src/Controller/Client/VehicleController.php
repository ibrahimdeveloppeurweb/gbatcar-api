<?php

namespace App\Controller\Client;

use App\Manager\Client\VehicleManager;
use App\Repository\Client\VehicleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route(path="/api/private/vehicle")
 */
class VehicleController extends AbstractController
{
    private $vehicleRepository;
    private $vehicleManager;

    public function __construct(
        VehicleRepository $vehicleRepository,
        VehicleManager $vehicleManager
        )
    {
        $this->vehicleRepository = $vehicleRepository;
        $this->vehicleManager = $vehicleManager;
    }

    /**
     * @Route("/", name="index_vehicle", methods={"GET"},
     * options={"description"="Liste des vehicle", "permission"="VEHICLE:LIST"})
     */
    public function index(Request $request)
    {
        $items = $this->vehicleRepository->findAll();
        return $this->json($items, 200, [], ['groups' => ["vehicle"]]);
    }

    /**
     * @Route("/new", name="new_vehicle", methods={"POST"}, 
     * options={"description"="Ajouter un nouveau vehicle", "permission"="VEHICLE:NEW"})
     */
    public function new (Request $request)
    {
    // To be implemented with VehicleManager
    }

    /**
     * @Route("/dashboard", name="dashboard_vehicle", methods={"GET"}, 
     * options={"description"="Statistiques du tableau de bord", "permission"="VEHICLE:DASHBOARD"})
     */
    public function dashboard()
    {
        // To be implemented
        return $this->json([], 200);
    }

    /**
     * @Route("/{uuid}/show", name="show_vehicle", methods={"GET"}, 
     * options={"description"="Détails d'un vehicle", "permission"="VEHICLE:SHOW"})
     */
    public function show($uuid)
    {
        $item = $this->vehicleRepository->findOneByUuid($uuid);
        if (!$item) {
            return $this->json(['message' => 'Not found'], 404);
        }
        return $this->json($item, 200, [], ['groups' => ["vehicle"]]);
    }

    /**
     * @Route("/{uuid}/edit", name="edit_vehicle", methods={"PUT", "POST"}, 
     * options={"description"="Modifier un vehicle", "permission"="VEHICLE:EDIT"})
     */
    public function edit(Request $request, $uuid)
    {
    // To be implemented with VehicleManager
    }

    /**
     * @Route("/{uuid}/delete", name="delete_vehicle", methods={"DELETE"},
     * options={"description"="Supprimer un vehicle", "permission"="VEHICLE:DELETE"})
     */
    public function delete($uuid)
    {
    // To be implemented with VehicleManager
    }

    /**
     * @Route("/{uuid}/reserve", name="reserve_vehicle", methods={"POST", "PUT"},
     * options={"description"="Réserver un véhicule", "permission"="VEHICLE:RESERVE"})
     */
    public function reserve(Request $request, $uuid)
    {
    // To be implemented
    }

    /**
     * @Route("/{uuid}/status", name="status_vehicle", methods={"POST", "PUT"},
     * options={"description"="Changer le statut d'un véhicule", "permission"="VEHICLE:CHANGE_STATUS"})
     */
    public function changeStatus(Request $request, $uuid)
    {
    // To be implemented
    }
}