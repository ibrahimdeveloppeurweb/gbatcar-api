<?php

namespace App\Controller\Client;

use App\Manager\Client\MaintenanceAlertManager;
use App\Repository\Client\MaintenanceAlertRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route(path="/api/private/maintenance/alert")
 */
class MaintenanceAlertController extends AbstractController
{
    private $maintenanceAlertRepository;
    private $maintenanceAlertManager;

    public function __construct(
        MaintenanceAlertRepository $maintenanceAlertRepository,
        MaintenanceAlertManager $maintenanceAlertManager
        )
    {
        $this->maintenanceAlertRepository = $maintenanceAlertRepository;
        $this->maintenanceAlertManager = $maintenanceAlertManager;
    }

    /**
     * @Route("/", name="index_maintenance-alert", methods={"GET"},
     * options={"description"="Liste des maintenance-alert", "permission"="MAINTENANCE_ALERT:LIST"})
     */
    public function index(Request $request)
    {
        $items = $this->maintenanceAlertRepository->findAll();
        return $this->json($items, 200, [], ['groups' => ["maintenance_alert"]]);
    }

    /**
     * @Route("/new", name="new_maintenance-alert", methods={"POST"}, 
     * options={"description"="Ajouter un nouveau maintenance-alert", "permission"="MAINTENANCE_ALERT:NEW"})
     */
    public function new (Request $request)
    {
    // To be implemented with MaintenanceAlertManager
    }

    /**
     * @Route("/dashboard", name="dashboard_maintenance-alert", methods={"GET"}, 
     * options={"description"="Statistiques du tableau de bord", "permission"="MAINTENANCE_ALERT:DASHBOARD"})
     */
    public function dashboard()
    {
        // To be implemented
        return $this->json([], 200);
    }

    /**
     * @Route("/{uuid}/show", name="show_maintenance-alert", methods={"GET"}, 
     * options={"description"="Détails d'un maintenance-alert", "permission"="MAINTENANCE_ALERT:SHOW"})
     */
    public function show($uuid)
    {
        $item = $this->maintenanceAlertRepository->findOneByUuid($uuid);
        if (!$item) {
            return $this->json(['message' => 'Not found'], 404);
        }
        return $this->json($item, 200, [], ['groups' => ["maintenance_alert"]]);
    }

    /**
     * @Route("/{uuid}/edit", name="edit_maintenance-alert", methods={"PUT", "POST"}, 
     * options={"description"="Modifier un maintenance-alert", "permission"="MAINTENANCE_ALERT:EDIT"})
     */
    public function edit(Request $request, $uuid)
    {
    // To be implemented with MaintenanceAlertManager
    }

    /**
     * @Route("/{uuid}/delete", name="delete_maintenance-alert", methods={"DELETE"},
     * options={"description"="Supprimer un maintenance-alert", "permission"="MAINTENANCE_ALERT:DELETE"})
     */
    public function delete($uuid)
    {
    // To be implemented with MaintenanceAlertManager
    }

    /**
     * @Route("/{uuid}/status", name="status_maintenance-alert", methods={"POST", "PUT"},
     * options={"description"="Changer le statut d'une alerte", "permission"="MAINTENANCE_ALERT:CHANGE_STATUS"})
     */
    public function changeStatus(Request $request, $uuid)
    {
    // To be implemented
    }
}