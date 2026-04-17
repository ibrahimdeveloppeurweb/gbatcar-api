<?php

namespace App\Controller\Client;

use App\Manager\Client\MaintenanceAlertManager;
use App\Repository\Client\MaintenanceAlertRepository;
use App\Entity\Client\Payment;
use App\Manager\Admin\AuditLogManager;
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
    private $auditLogManager;

    public function __construct(
        MaintenanceAlertRepository $maintenanceAlertRepository,
        MaintenanceAlertManager $maintenanceAlertManager,
        AuditLogManager $auditLogManager
        )
    {
        $this->maintenanceAlertRepository = $maintenanceAlertRepository;
        $this->maintenanceAlertManager = $maintenanceAlertManager;
        $this->auditLogManager = $auditLogManager;
    }

    /**
     * @Route("/", name="index_maintenance-alert", methods={"GET"},
     * options={"description"="Liste des maintenance-alert", "permission"="MAINTENANCE_ALERT:LIST"})
     */
    public function index(Request $request)
    {
        $data = (object)$request->query->all();
        $items = $this->maintenanceAlertRepository->findByFilters((array)$data);
        return $this->json($items, 200, [], ['groups' => ["alert"]]);
    }

    /**
     * @Route("/new", name="new_maintenance-alert", methods={"POST"}, 
     * options={"description"="Ajouter un nouveau maintenance-alert", "permission"="MAINTENANCE_ALERT:NEW"})
     */
    public function new (Request $request)
    {
        try {
            $raw = $request->getContent();
            $data = json_decode($raw);
            if (!$data) {
                $data = (object)$request->request->all();
            }

            $maintenanceAlert = $this->maintenanceAlertManager->create($data, $request);

            $this->auditLogManager->log(
                'Sinistre',
                'Création',
                sprintf('Déclaration d\'un sinistre : %s', substr($maintenanceAlert->getDescription(), 0, 50))
            );

            return $this->json($maintenanceAlert, 201, [], ['groups' => ["alert"]]);
        }
        catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * @Route("/dashboard", name="dashboard_maintenance-alert", methods={"GET"}, 
     * options={"description"="Statistiques du tableau de bord", "permission"="MAINTENANCE_ALERT:DASHBOARD"})
     */
    public function dashboard()
    {
        // For now, return basic counts or structure
        $items = $this->maintenanceAlertRepository->findAll();
        $stats = [
            'total' => count($items),
            'open' => count(array_filter($items, fn($i) => $i->getStatus() === 'Ouvert')),
            'in_progress' => count(array_filter($items, fn($i) => $i->getStatus() === 'En cours')),
            'resolved' => count(array_filter($items, fn($i) => $i->getStatus() === 'Résolu')),
        ];
        return $this->json($stats, 200);
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
        return $this->json($item, 200, [], ['groups' => ["alert"]]);
    }

    /**
     * @Route("/{uuid}/edit", name="edit_maintenance-alert", methods={"PUT", "POST"}, 
     * options={"description"="Modifier un maintenance-alert", "permission"="MAINTENANCE_ALERT:EDIT"})
     */
    public function edit(Request $request, $uuid)
    {
        try {
            $raw = $request->getContent();
            $data = json_decode($raw);
            if (!$data) {
                $data = (object)$request->request->all();
            }

            $item = $this->maintenanceAlertManager->update($uuid, $data, $request);

            $this->auditLogManager->log(
                'Sinistre',
                'Modification',
                sprintf('Mise à jour du sinistre : %s', substr($item->getDescription(), 0, 50))
            );

            return $this->json($item, 200, [], ['groups' => ["alert"]]);
        }
        catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * @Route("/{uuid}/delete", name="delete_maintenance-alert", methods={"DELETE"},
     * options={"description"="Supprimer un maintenance-alert", "permission"="MAINTENANCE_ALERT:DELETE"})
     */
    public function delete($uuid)
    {
        try {
            $item = $this->maintenanceAlertRepository->findOneByUuid($uuid);
            if ($item) {
                $this->maintenanceAlertManager->delete($item);

                $this->auditLogManager->log(
                    'Sinistre',
                    'Suppression',
                    'Suppression d\'un sinistre'
                );
            }
            return $this->json(['message' => 'Alerte supprimée'], 200);
        }
        catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * @Route("/{uuid}/status", name="status_maintenance-alert", methods={"POST", "PUT"},
     * options={"description"="Changer le statut d'une alerte", "permission"="MAINTENANCE_ALERT:CHANGE_STATUS"})
     */
    public function changeStatus(Request $request, $uuid)
    {
        try {
            $raw = $request->getContent();
            $data = json_decode($raw);
            if (!$data) {
                $data = (object)$request->request->all();
            }

            $status = $data->status ?? 'Ouvert';
            $item = $this->maintenanceAlertManager->changeStatus($uuid, $status);

            $this->auditLogManager->log(
                'Sinistre',
                'Changement de statut',
                sprintf('Le statut du sinistre est passé à %s', $status)
            );

            return $this->json($item, 200, [], ['groups' => ["alert"]]);
        }
        catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * @Route("/{uuid}/invoice", name="invoice_maintenance-alert", methods={"POST"},
     * options={"description"="Facturer un sinistre", "permission"="MAINTENANCE_ALERT:INVOICE"})
     */
    public function invoice(Request $request, $uuid)
    {
        try {
            $raw = $request->getContent();
            $data = json_decode($raw);
            if (!$data) {
                $data = (object)$request->request->all();
            }

            $payer = $data->payer ?? 'SOCIETE';
            $item = $this->maintenanceAlertManager->invoice($uuid, $payer);

            $this->auditLogManager->log(
                'Sinistre',
                'Facturation',
                sprintf('Sinistre facturé à : %s', $payer)
            );

            return $this->json($item, 200, [], ['groups' => ["alert"]]);
        }
        catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        }
    }
}