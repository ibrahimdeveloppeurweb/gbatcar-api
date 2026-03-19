<?php

namespace App\Controller\Client;

use App\Entity\Client\MaintenanceDocument;
use App\Manager\Client\MaintenanceManager;
use App\Repository\Client\MaintenanceDocumentRepository;
use App\Repository\Client\MaintenanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route(path="/api/private/maintenance")
 */
class MaintenanceController extends AbstractController
{
    private $maintenanceRepository;
    private $maintenanceManager;
    private $em;
    private $documentRepository;

    public function __construct(
        MaintenanceRepository $maintenanceRepository,
        MaintenanceManager $maintenanceManager,
        EntityManagerInterface $em,
        MaintenanceDocumentRepository $documentRepository
        )
    {
        $this->maintenanceRepository = $maintenanceRepository;
        $this->maintenanceManager = $maintenanceManager;
        $this->em = $em;
        $this->documentRepository = $documentRepository;
    }

    /**
     * @Route("/", name="index_maintenance", methods={"GET"},
     * options={"description"="Liste des maintenance", "permission"="MAINTENANCE:LIST"})
     */
    public function index(Request $request)
    {
        $filters = [
            'search' => $this->sanitize($request->query->get('search')),
            'status' => $this->sanitize($request->query->get('status')),
            'dateMin' => $this->sanitize($request->query->get('dateMin')),
            'dateMax' => $this->sanitize($request->query->get('dateMax')),
            'costMin' => $this->sanitize($request->query->get('costMin')),
            'costMax' => $this->sanitize($request->query->get('costMax')),
            'limit' => $request->query->get('limit', 50),
        ];
        $items = $this->maintenanceRepository->findByFilters($filters);
        return $this->json($items, 200, [], ['groups' => ["maintenance"]]);
    }

    /** Convert JS 'undefined'/'null' string into PHP null */
    private function sanitize(?string $value): ?string
    {
        if ($value === null || $value === 'undefined' || $value === 'null' || $value === '') {
            return null;
        }
        return $value;
    }

    /**
     * @Route("/new", name="new_maintenance", methods={"POST"}, 
     * options={"description"="Ajouter un nouveau maintenance", "permission"="MAINTENANCE:NEW"})
     */
    public function new (Request $request)
    {
        try {
            $data = json_decode($request->getContent());
            $item = $this->maintenanceManager->create($data);
            return $this->json($item, 201, [], ['groups' => ["maintenance"]]);
        }
        catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * @Route("/dashboard", name="dashboard_maintenance", methods={"GET"}, 
     * options={"description"="Statistiques du tableau de bord", "permission"="MAINTENANCE:DASHBOARD"})
     */
    public function dashboard()
    {
        $metrics = $this->maintenanceRepository->getDashboardMetrics();
        return $this->json($metrics, 200);
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
        try {
            $data = json_decode($request->getContent());
            $item = $this->maintenanceManager->update($uuid, $data);
            return $this->json($item, 200, [], ['groups' => ["maintenance"]]);
        }
        catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * @Route("/{uuid}/delete", name="delete_maintenance", methods={"DELETE"},
     * options={"description"="Supprimer un maintenance", "permission"="MAINTENANCE:DELETE"})
     */
    public function delete($uuid)
    {
        try {
            $item = $this->maintenanceRepository->findOneByUuid($uuid);
            if ($item) {
                $this->maintenanceManager->delete($item);
            }
            return $this->json(['message' => 'Intervention supprimée'], 200);
        }
        catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        }
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
    /**
     * @Route("/{uuid}/documents", name="upload_maintenance_document", methods={"POST"},
     * options={"description"="Upload document(s) pour une maintenance", "permission"="MAINTENANCE:DOCUMENT"})
     */
    public function uploadDocument(Request $request, string $uuid)
    {
        $maintenance = $this->maintenanceRepository->findOneByUuid($uuid);
        if (!$maintenance) {
            return $this->json(['message' => 'Intervention introuvable'], 404);
        }

        $files = $request->files->get('files');
        if (!$files) {
            return $this->json(['message' => 'Aucun fichier reçu'], 400);
        }
        if (!is_array($files)) {
            $files = [$files];
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/maintenance/' . $uuid . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $saved = [];
        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getClientMimeType();
            $size = $file->getSize();
            $storedName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);

            $file->move($uploadDir, $storedName);

            $doc = new MaintenanceDocument();
            $doc->setOriginalName($originalName);
            $doc->setStoredName($storedName);
            $doc->setMimeType($mimeType);
            $doc->setSize($size);
            $doc->setMaintenance($maintenance);
            $this->em->persist($doc);
            $saved[] = ['name' => $originalName, 'uuid' => $doc->getUuid()];
        }
        $this->em->flush();
        return $this->json(['message' => count($saved) . ' fichier(s) ajouté(s)', 'files' => $saved], 201);
    }

    /**
     * @Route("/{mUuid}/documents/{dUuid}/download", name="download_maintenance_document", methods={"GET"},
     * options={"description"="Télécharger un document", "permission"="MAINTENANCE:DOCUMENT"})
     */
    public function downloadDocument(string $mUuid, string $dUuid)
    {
        try {
            $doc = $this->documentRepository->findOneBy(['uuid' => $dUuid]);
            if (!$doc) {
                return $this->json(['message' => 'Document introuvable'], 404);
            }
            $path = $this->getParameter('kernel.project_dir') . '/public/uploads/maintenance/' . $mUuid . '/' . $doc->getStoredName();
            if (!file_exists($path)) {
                return $this->json(['message' => 'Fichier introuvable sur le serveur'], 404);
            }
            $response = new BinaryFileResponse($path);

            // Set mime type manually to avoid guessing error (missing symfony/mime)
            if ($doc->getMimeType()) {
                $response->headers->set('Content-Type', $doc->getMimeType());
            }

            // Generate a safe fallback name (ASCII only)
            $fallbackName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $doc->getOriginalName());

            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $doc->getOriginalName(),
                $fallbackName
            );
            return $response;
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors du téléchargement : ' . $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/{mUuid}/documents/{dUuid}/delete", name="delete_maintenance_document", methods={"DELETE"},
     * options={"description"="Supprimer un document", "permission"="MAINTENANCE:DOCUMENT"})
     */
    public function deleteDocument(string $mUuid, string $dUuid)
    {
        $doc = $this->documentRepository->findOneBy(['uuid' => $dUuid]);
        if (!$doc) {
            return $this->json(['message' => 'Document introuvable'], 404);
        }
        $path = $this->getParameter('kernel.project_dir') . '/public/uploads/maintenance/' . $mUuid . '/' . $doc->getStoredName();
        if (file_exists($path)) {
            @unlink($path);
        }
        $this->em->remove($doc);
        $this->em->flush();
        return $this->json(['message' => 'Document supprimé'], 200);
    }
}