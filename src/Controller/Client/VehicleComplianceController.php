<?php

namespace App\Controller\Client;

use App\Entity\Client\VehicleCompliance;
use App\Manager\Client\VehicleComplianceManager;
use App\Repository\Client\VehicleComplianceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Manager\Admin\AuditLogManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route(path="/api/private/vehicle-compliance")
 */
class VehicleComplianceController extends AbstractController
{
    private $manager;
    private $repository;
    private $serializer;
    private $auditLogManager;

    public function __construct(
        VehicleComplianceManager $manager,
        VehicleComplianceRepository $repository,
        SerializerInterface $serializer,
        AuditLogManager $auditLogManager
        )
    {
        $this->manager = $manager;
        $this->repository = $repository;
        $this->serializer = $serializer;
        $this->auditLogManager = $auditLogManager;
    }

    /**
     * @Route("/", name="index_vehicle_compliance", methods={"GET"},
     * options={"description"="Liste des conformités", "permission"="VEHICLE:COMPLIANCE:LIST"})
     */
    public function index(Request $request)
    {
        $data = (object)$request->query->all();
        $items = $this->repository->findByFilters($data);
        return $this->json($items, 200, [], ['groups' => ["compliance"]]);
    }

    /**
     * @Route("/new", name="new_vehicle_compliance", methods={"POST"}, 
     * options={"description"="Ajouter une conformité", "permission"="VEHICLE:COMPLIANCE:ADD"})
     */
    public function new (Request $request)
    {
        $raw = $request->getContent();
        $data = $raw ? json_decode($raw) : (object)$request->request->all();
        if (!$data)
            $data = new \stdClass();
        try {
            $compliance = $this->manager->create($data, $request);

            $this->auditLogManager->log(
                'Véhicule',
                'Création',
                sprintf('Ajout d\'une conformité : %s pour le véhicule %s', $compliance->getType(), $compliance->getVehicle()->getImmatriculation())
            );

            return $this->json($compliance, 201, [], ['groups' => ['compliance']]);
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la création.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/{uuid}/show", name="show_vehicle_compliance", methods={"GET"}, 
     * options={"description"="Détails d'une conformité", "permission"="VEHICLE:COMPLIANCE:SHOW"})
     */
    public function show(Request $request, $uuid)
    {
        $filters = (object)[
            'searchTerm' => $request->query->get('searchTerm', ''),
            'statusFilter' => $request->query->get('statusFilter', ''),
            'startDate' => $request->query->get('startDate'),
            'endDate' => $request->query->get('endDate'),
        ];

        $item = is_numeric($uuid)
            ? $this->repository->find($uuid)
            : $this->repository->findOneByUuid($uuid);

        if (!$item) {
            return $this->json(['message' => 'Not found'], 404);
        }

        // Manual normalization to override collections
        $data = $this->serializer->normalize($item, null, ['groups' => ["compliance"]]);

        $filtered = $this->manager->getFilteredCollections($item, $filters);

        if (isset($data['vehicle'])) {
            $data['vehicle']['complianceDocuments'] = $this->serializer->normalize($filtered['history'], null, ['groups' => ["compliance"]]);
            $data['vehicle']['penalties'] = $this->serializer->normalize($filtered['penalties'], null, ['groups' => ["compliance"]]);
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/{uuid}/edit", name="edit_vehicle_compliance", methods={"PUT", "POST"}, 
     * options={"description"="Modifier une conformité", "permission"="VEHICLE:COMPLIANCE:EDIT"})
     */
    public function edit(Request $request, $uuid)
    {
        $raw = $request->getContent();
        $data = $raw ? json_decode($raw) : (object)$request->request->all();
        if (!$data)
            $data = new \stdClass();
        try {
            $compliance = $this->manager->update($uuid, $data, $request);

            $this->auditLogManager->log(
                'Véhicule',
                'Modification',
                sprintf('Modification de la conformité : %s', $compliance->getType())
            );

            return $this->json($compliance, 200, [], ['groups' => ['compliance']]);
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la modification.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/{uuid}/delete", name="delete_vehicle_compliance", methods={"DELETE"},
     * options={"description"="Supprimer une conformité", "permission"="VEHICLE:COMPLIANCE:DELETE"})
     */
    public function delete($uuid)
    {
        $compliance = is_numeric($uuid)
            ? $this->repository->find($uuid)
            : $this->repository->findOneByUuid($uuid);

        if (!$compliance) {
            return $this->json(['message' => 'Conformité introuvable.'], 404);
        }
        try {
            $type = $compliance->getType();
            $this->manager->delete($compliance);

            $this->auditLogManager->log(
                'Véhicule',
                'Suppression',
                sprintf('Suppression de la conformité : %s', $type)
            );

            return $this->json(['message' => 'Conformité supprimée avec succès.'], 200);
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la suppression.', 'details' => $e->getMessage()], 500);
        }
    }
}