<?php

namespace App\Controller\Client;

use App\Manager\Client\VehicleManager;
use App\Repository\Client\VehicleRepository;
use App\Manager\Admin\AuditLogManager;
use Doctrine\ORM\EntityManagerInterface;
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
    private $em;
    private $auditLogManager;

    public function __construct(
        VehicleRepository $vehicleRepository,
        VehicleManager $vehicleManager,
        EntityManagerInterface $em,
        AuditLogManager $auditLogManager
        )
    {
        $this->vehicleRepository = $vehicleRepository;
        $this->vehicleManager = $vehicleManager;
        $this->em = $em;
        $this->auditLogManager = $auditLogManager;
    }

    /**
     * @Route("/", name="index_vehicle", methods={"GET"},
     * options={"description"="Liste des vehicle", "permission"="VEHICLE:LIST"})
     */
    public function index(Request $request)
    {
        $data = (object) $request->query->all();
        $items = $this->vehicleRepository->findCatalogByFilters($data);
        return $this->json($items, 200, [], ['groups' => ["vehicle"]]);
    }

    /**
     * @Route("/new", name="new_vehicle", methods={"POST"}, 
     * options={"description"="Ajouter un nouveau vehicle", "permission"="VEHICLE:NEW"})
     */
    public function new (Request $request)
    {
        // Supporte JSON et multipart/form-data
        $raw = $request->getContent();
        $data = $raw ? json_decode($raw) : (object) $request->request->all();
        if (!$data) $data = new \stdClass();
        try {
            $vehicle = $this->vehicleManager->create($data, $request);
            
            $this->auditLogManager->log(
                'Véhicule',
                'Création',
                sprintf('Nouveau véhicule ajouté : %s %s (%s)', $vehicle->getMarque(), $vehicle->getModele(), $vehicle->getImmatriculation())
            );

            return $this->json($vehicle, 201, [], ['groups' => ['vehicle']]);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la création du véhicule.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/dashboard", name="dashboard_vehicle", methods={"GET"}, 
     * options={"description"="Statistiques du tableau de bord", "permission"="VEHICLE:DASHBOARD"})
     */
    public function dashboard(Request $request)
    {
        $months = $request->query->getInt('months', 6);
        $data = $this->vehicleManager->getDashboardData($months);
        return $this->json($data, 200, [], ['groups' => ["vehicle"]]);
    }

    /**
     * @Route("/{uuid}/show", name="show_vehicle", methods={"GET"}, 
     * options={"description"="Détails d'un vehicle", "permission"="VEHICLE:SHOW"})
     */
    public function show($uuid)
    {
        $item = is_numeric($uuid) 
            ? $this->vehicleRepository->find($uuid)
            : $this->vehicleRepository->findOneByUuid($uuid);

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
        $raw = $request->getContent();
        $data = $raw ? json_decode($raw) : (object) $request->request->all();
        if (!$data) $data = new \stdClass();
        try {
            $vehicle = $this->vehicleManager->update($uuid, $data, $request);
            
            $this->auditLogManager->log(
                'Véhicule',
                'Modification',
                sprintf('Mise à jour du véhicule : %s %s', $vehicle->getMarque(), $vehicle->getModele())
            );

            return $this->json($vehicle, 200, [], ['groups' => ['vehicle']]);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la modification du véhicule.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/{uuid}/delete", name="delete_vehicle", methods={"DELETE"},
     * options={"description"="Supprimer un vehicle", "permission"="VEHICLE:DELETE"})
     */
    public function delete($uuid)
    {
        $vehicle = is_numeric($uuid) 
            ? $this->vehicleRepository->find($uuid)
            : $this->vehicleRepository->findOneByUuid($uuid);

        if (!$vehicle) {
            return $this->json(['message' => 'Véhicule introuvable.'], 404);
        }
        try {
            $name = $vehicle->getMarque() . ' ' . $vehicle->getModele();
            $this->vehicleManager->delete($vehicle);
            
            $this->auditLogManager->log(
                'Véhicule',
                'Suppression',
                sprintf('Suppression du véhicule : %s', $name)
            );

            return $this->json(['message' => 'Véhicule supprimé avec succès.'], 200);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la suppression.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/{uuid}/reserve", name="reserve_vehicle", methods={"POST", "PUT"},
     * options={"description"="Réserver un véhicule", "permission"="VEHICLE:RESERVE"})
     */
    public function reserve(Request $request, $uuid)
    {
        $vehicle = is_numeric($uuid) 
            ? $this->vehicleRepository->find($uuid)
            : $this->vehicleRepository->findOneByUuid($uuid);

        if (!$vehicle) {
            return $this->json(['message' => 'Véhicule introuvable.'], 404);
        }

        $data = json_decode($request->getContent());
        $reservedBy = $data->reservedBy ?? 'Agent';

        try {
            $this->vehicleManager->reserve($vehicle, $reservedBy);
            
            $this->auditLogManager->log(
                'Véhicule',
                'Réservation',
                sprintf('Réservation du véhicule : %s pour %s', $vehicle->getImmatriculation(), $reservedBy)
            );

            return $this->json($vehicle, 200, [], ['groups' => ['vehicle']]);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la réservation.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/{uuid}/status", name="status_vehicle", methods={"POST", "PUT"},
     * options={"description"="Changer le statut d'un véhicule", "permission"="VEHICLE:CHANGE_STATUS"})
     */
    public function changeStatus(Request $request, $uuid)
    {
        $vehicle = is_numeric($uuid) 
            ? $this->vehicleRepository->find($uuid)
            : $this->vehicleRepository->findOneByUuid($uuid);

        if (!$vehicle) {
            return $this->json(['message' => 'Véhicule introuvable.'], 404);
        }

        $data = json_decode($request->getContent());
        if (!isset($data->status)) {
            return $this->json(['message' => 'Le statut est obligatoire.'], 400);
        }

        $newStatus = $data->status;
        $vehicle->setStatut($newStatus);
        
        if ($newStatus === 'Disponible') {
            $vehicle->setPreReservedBy(null);
        }
        
        $this->em->flush();

        $this->auditLogManager->log(
            'Véhicule',
            'Changement de statut',
            sprintf('Le statut du véhicule %s est passé à %s', $vehicle->getImmatriculation(), $newStatus)
        );

        return $this->json($vehicle, 200, [], ['groups' => ['vehicle']]);
    }

    /**
     * @Route("/catalog", name="vehicle_catalog_list", methods={"GET"}, 
     * options={"description"="Liste du catalogue de véhicules", "permission"="VEHICLE:CATALOG:LIST"})
     */
    public function getCatalog(Request $request)
    {
        $data = (object) $request->query->all();
        $items = $this->vehicleRepository->findCatalogByFilters($data);
        return $this->json(['data' => $items], 200, [], ['groups' => ["vehicle"]]);
    }

    /**
     * @Route("/catalog/new", name="vehicle_catalog_new", methods={"POST"}, 
     * options={"description"="Ajouter un véhicule au catalogue", "permission"="VEHICLE:CATALOG:NEW"})
     */
    public function addCatalog(Request $request)
    {
        return $this->json(['message' => 'Created'], 201);
    }

    /**
     * @Route("/compliance", name="vehicle_compliance_list", methods={"GET"}, 
     * options={"description"="Afficher les véhicules en visite et conformité", "permission"="VEHICLE:COMPLIANCE:LIST"})
     */
    public function getComplianceList(Request $request)
    {
        return $this->json(['data' => []], 200);
    }

    /**
     * @Route("/brand-images/{brand}", name="vehicle_brand_images_list", methods={"GET"}, 
     * options={"description"="Lister les images par marque", "permission"="VEHICLE:BRAND:IMAGES:LIST"})
     */
    public function getBrandImages(string $brand)
    {
        $brandDir = $this->getParameter('kernel.project_dir') . '/public/uploads/vehicles/brands/' . strtolower(trim($brand));
        
        if (!is_dir($brandDir)) {
            return $this->json(['data' => []], 200);
        }

        $files = array_diff(scandir($brandDir), array('..', '.'));
        $images = [];
        foreach ($files as $file) {
            $images[] = '/uploads/vehicles/brands/' . strtolower(trim($brand)) . '/' . $file;
        }

        return $this->json(['data' => array_values($images)], 200);
    }

    /**
     * @Route("/brand-images/{brand}", name="vehicle_brand_images_upload", methods={"POST"}, 
     * options={"description"="Uploader une image pour une marque", "permission"="VEHICLE:BRAND:IMAGES:NEW"})
     */
    public function uploadBrandImage(Request $request, string $brand)
    {
        $file = $request->files->get('file');

        if (!$file) {
            return $this->json(['message' => 'Aucun fichier fourni'], 400);
        }

        $brandDir = $this->getParameter('kernel.project_dir') . '/public/uploads/vehicles/brands/' . strtolower(trim($brand));
        
        if (!is_dir($brandDir)) {
            mkdir($brandDir, 0775, true);
        }

        $filename = uniqid() . '_' . $file->getClientOriginalName();
        $file->move($brandDir, $filename);

        $imageUrl = '/uploads/vehicles/brands/' . strtolower(trim($brand)) . '/' . $filename;

        return $this->json([
            'message' => 'Image uploadée avec succès', 
            'url' => $imageUrl
        ], 201);
    }

    /**
     * @Route("/{id_or_uuid}/cover-image", name="vehicle_set_cover_image", methods={"PUT"},
     * options={"description"="Définit une image comme couverture du véhicule", "permission"="VEHICLE:EDIT"})
     */
    public function setCoverImage($id_or_uuid, Request $request)
    {
        $vehicle = is_numeric($id_or_uuid) 
            ? $this->vehicleRepository->find($id_or_uuid)
            : $this->vehicleRepository->findOneByUuid($id_or_uuid);

        if (!$vehicle) {
            return $this->json(['message' => 'Véhicule non trouvé'], 404);
        }

        $data = json_decode($request->getContent());
        if (!$data || !isset($data->photo)) {
            return $this->json(['message' => 'URL de la photo manquante'], 400);
        }

        $vehicle->setPhoto($data->photo);

        // Reordonner les photos pour que la couverture soit en première position
        $photos = $vehicle->getPhotos() ?? [];
        $targetPhoto = trim($data->photo);
        
        $foundKey = false;
        foreach ($photos as $key => $p) {
            if (trim($p) === $targetPhoto) {
                unset($photos[$key]);
                $foundKey = true;
                break;
            }
        }
        
        if ($foundKey) {
            array_unshift($photos, $data->photo);
            $vehicle->setPhotos(array_values($photos));
        }

        $this->em->flush();

        return $this->json(['message' => 'Photo de couverture mise à jour', 'photo' => $data->photo, 'photos' => $vehicle->getPhotos()], 200);
    }

    /**
     * @Route("/{id_or_uuid}/photo", name="vehicle_remove_photo", methods={"DELETE"},
     * options={"description"="Supprime une photo du tableau photos du véhicule", "permission"="VEHICLE:EDIT"})
     */
    public function removePhoto($id_or_uuid, Request $request)
    {
        $vehicle = is_numeric($id_or_uuid) 
            ? $this->vehicleRepository->find($id_or_uuid)
            : $this->vehicleRepository->findOneByUuid($id_or_uuid);

        if (!$vehicle) {
            return $this->json(['message' => 'Véhicule non trouvé'], 404);
        }

        // Photo can come from query string (for DELETE) or body
        $photo = $request->query->get('photo');
        if (!$photo) {
            $data = json_decode($request->getContent());
            $photo = $data?->photo ?? null;
        }

        if (!$photo) {
            return $this->json(['message' => 'URL de la photo manquante'], 400);
        }

        $photos = $vehicle->getPhotos() ?? [];
        $targetPhoto = trim($photo);
        $photos = array_values(array_filter($photos, fn($p) => trim($p) !== $targetPhoto));
        $vehicle->setPhotos($photos);

        // Si c'était aussi la couverture, effacer
        if (trim((string)$vehicle->getPhoto()) === $targetPhoto) {
            $vehicle->setPhoto(count($photos) > 0 ? $photos[0] : null);
        }

        $this->em->flush();

        return $this->json(['message' => 'Photo supprimée', 'photos' => $photos], 200);
    }
}