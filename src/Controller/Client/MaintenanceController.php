<?php

namespace App\Controller\Client;

use App\Manager\Client\MaintenanceManager;
use App\Repository\Client\MaintenanceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route(path="/api/private/maintenance")
 */
class MaintenanceController extends AbstractController
{
    private $maintenanceRepository;
    private $maintenanceManager;

    public function __construct(
        MaintenanceRepository $maintenanceRepository,
        MaintenanceManager $maintenanceManager
        )
    {
        $this->maintenanceRepository = $maintenanceRepository;
        $this->maintenanceManager = $maintenanceManager;
    }

    /**
     * @Route("/", name="index_maintenance", methods={"GET"},
     * options={"description"="Liste des maintenance", "permission"="MAINTENANCE:LIST"})
     */
    public function index(Request $request)
    {
        $items = $this->maintenanceRepository->findAll();
        return $this->json($items, 200, [], ['groups' => ["maintenance"]]);
    }

    /**
     * @Route("/new", name="new_maintenance", methods={"POST"}, 
     * options={"description"="Ajouter un nouveau maintenance", "permission"="MAINTENANCE:NEW"})
     */
    public function new (Request $request)
    {
    // To be implemented with MaintenanceManager
    }

    /**
     * @Route("/dashboard", name="dashboard_maintenance", methods={"GET"}, 
     * options={"description"="Statistiques du tableau de bord", "permission"="MAINTENANCE:DASHBOARD"})
     */
    public function dashboard()
    {
        // To be implemented
        return $this->json([], 200);
    }

    /**
     * @Route("/{uuid}/show", name="show_maintenance", methods={"GET"}, 
     * options={"description"="Détails d'un maintenance", "permission"="MAINTENANCE:SHOW"})
     */
    public function show($uuid)
    {
        $item = $this->maintenanceRepository->findOneByUuid($uuid);
        if (!$item) {
            return $this->json(['message' => 'Not found'], 404);
        }
        return $this->json($item, 200, [], ['groups' => ["maintenance"]]);
    }

    /**
     * @Route("/{uuid}/edit", name="edit_maintenance", methods={"PUT", "POST"}, 
     * options={"description"="Modifier un maintenance", "permission"="MAINTENANCE:EDIT"})
     */
    public function edit(Request $request, $uuid)
    {
    // To be implemented with MaintenanceManager
    }

    /**
     * @Route("/{uuid}/delete", name="delete_maintenance", methods={"DELETE"},
     * options={"description"="Supprimer un maintenance", "permission"="MAINTENANCE:DELETE"})
     */
    public function delete($uuid)
    {
    // To be implemented with MaintenanceManager
    }

    /**
     * @Route("/{uuid}/status", name="status_maintenance", methods={"POST", "PUT"},
     * options={"description"="Changer le statut d'une maintenance", "permission"="MAINTENANCE:CHANGE_STATUS"})
     */
    public function changeStatus(Request $request, $uuid)
    {
    // To be implemented
    }

    /**
     * @Route("/alerts", name="alerts_maintenance", methods={"GET"},
     * options={"description"="Liste des alertes depuis le module maintenance", "permission"="MAINTENANCE:ALERTS"})
     */
    public function alerts()
    {
        // To be implemented
        return $this->json([], 200);
    }
}