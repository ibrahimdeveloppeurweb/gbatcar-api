<?php

namespace App\Controller\Client;

use App\Manager\Client\PenaltyManager;
use App\Repository\Client\PenaltyRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route(path="/api/private/penalty")
 */
class PenaltyController extends AbstractController
{
    private $penaltyRepository;
    private $penaltyManager;

    public function __construct(
        PenaltyRepository $penaltyRepository,
        PenaltyManager $penaltyManager
        )
    {
        $this->penaltyRepository = $penaltyRepository;
        $this->penaltyManager = $penaltyManager;
    }

    /**
     * @Route("/", name="index_penalty", methods={"GET"},
     * options={"description"="Liste des penalty", "permission"="PENALTY:LIST"})
     */
    public function index(Request $request)
    {
        $data = (object)$request->query->all();
        $items = $this->penaltyRepository->findByFilters($data);
        return $this->json($items, 200, [], ['groups' => ["penalty"]]);
    }

    /**
     * @Route("/new", name="new_penalty", methods={"POST"}, 
     * options={"description"="Ajouter un nouveau penalty", "permission"="PENALTY:NEW"})
     */
    public function new (Request $request)
    {
        $data = (object)$request->request->all();
        if (empty((array)$data)) {
            $data = json_decode($request->getContent());
        }

        if (!$data)
            $data = new \stdClass();

        try {
            $penalty = $this->penaltyManager->create($data, $request);
            return $this->json($penalty, 201, [], ['groups' => ['penalty']]);
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la création de la pénalité.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/dashboard", name="dashboard_penalty", methods={"GET"}, 
     * options={"description"="Statistiques du tableau de bord", "permission"="PENALTY:DASHBOARD"})
     */
    public function dashboard()
    {
        return $this->json([], 200);
    }

    /**
     * @Route("/{uuid}/show", name="show_penalty", methods={"GET"}, 
     * options={"description"="Détails d'un penalty", "permission"="PENALTY:SHOW"})
     */
    public function show($uuid)
    {
        $item = $this->penaltyRepository->findOneBy(['uuid' => $uuid]);
        if (!$item) {
            return $this->json(['message' => 'Not found'], 404);
        }
        return $this->json($item, 200, [], ['groups' => ["penalty"]]);
    }

    /**
     * @Route("/{uuid}/edit", name="edit_penalty", methods={"PUT", "POST"}, 
     * options={"description"="Modifier un penalty", "permission"="PENALTY:EDIT"})
     */
    public function edit(Request $request, $uuid)
    {
        $data = (object)$request->request->all();
        if (empty((array)$data)) {
            $data = json_decode($request->getContent());
        }

        if (!$data)
            $data = new \stdClass();

        try {
            $penalty = $this->penaltyManager->update($uuid, $data, $request);
            return $this->json($penalty, 200, [], ['groups' => ['penalty']]);
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la modification de la pénalité.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/{uuid}/delete", name="delete_penalty", methods={"DELETE"},
     * options={"description"="Supprimer un penalty", "permission"="PENALTY:DELETE"})
     */
    public function delete($uuid)
    {
        $penalty = $this->penaltyRepository->findOneBy(['uuid' => $uuid]);
        if (!$penalty) {
            return $this->json(['message' => 'Pénalité introuvable.'], 404);
        }

        try {
            $this->penaltyManager->delete($penalty);
            return $this->json(['message' => 'Pénalité supprimée avec succès.'], 200);
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la suppression.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/{uuid}/status", name="status_penalty", methods={"POST", "PUT"},
     * options={"description"="Marquer une pénalité comme payée", "permission"="PENALTY:CHANGE_STATUS"})
     */
    public function changeStatus(Request $request, $uuid)
    {
        return $this->json(['message' => 'Status changed'], 200);
    }
}