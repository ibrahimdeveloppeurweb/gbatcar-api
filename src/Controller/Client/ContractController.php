<?php

namespace App\Controller\Client;

use App\Manager\Client\ContractManager;
use App\Repository\Client\ContractRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route(path="/api/private/contract")
 */
class ContractController extends AbstractController
{
    private $contractRepository;
    private $contractManager;

    public function __construct(
        ContractRepository $contractRepository,
        ContractManager $contractManager
        )
    {
        $this->contractRepository = $contractRepository;
        $this->contractManager = $contractManager;
    }

    /**
     * @Route("/", name="index_contract", methods={"GET"},
     * options={"description"="Liste des contract", "permission"="CONTRACT:LIST"})
     */
    public function index(Request $request)
    {
        $filters = $request->query->all();
        $items = $this->contractRepository->findByFilters($filters);
        return $this->json($items, 200, [], ['groups' => ["contract"]]);
    }

    /**
     * @Route("/new", name="new_contract", methods={"POST"}, 
     * options={"description"="Ajouter un nouveau contract", "permission"="CONTRACT:NEW"})
     */
    public function new (Request $request)
    {
        $data = json_decode($request->getContent());
        $item = $this->contractManager->create($data);
        return $this->json($item, 201, [], ['groups' => ["contract"]]);
    }

    /**
     * @Route("/dashboard", name="dashboard_contract", methods={"GET"}, 
     * options={"description"="Statistiques du tableau de bord", "permission"="CONTRACT:DASHBOARD"})
     */
    public function dashboard()
    {
        // To be implemented
        return $this->json([], 200);
    }

    /**
     * @Route("/{uuid}/show", name="show_contract", methods={"GET"}, 
     * options={"description"="Détails d'un contract", "permission"="CONTRACT:SHOW"})
     */
    public function show($uuid)
    {
        $item = $this->contractRepository->findOneByUuid($uuid);
        if (!$item) {
            return $this->json(['message' => 'Not found'], 404);
        }
        return $this->json($item, 200, [], ['groups' => ["contract"]]);
    }

    /**
     * @Route("/{uuid}/edit", name="edit_contract", methods={"PUT", "POST"}, 
     * options={"description"="Modifier un contract", "permission"="CONTRACT:EDIT"})
     */
    public function edit(Request $request, $uuid)
    {
        $data = json_decode($request->getContent());
        $item = $this->contractManager->update($uuid, $data);
        return $this->json($item, 200, [], ['groups' => ["contract"]]);
    }

    /**
     * @Route("/{uuid}/delete", name="delete_contract", methods={"DELETE"},
     * options={"description"="Supprimer un contract", "permission"="CONTRACT:DELETE"})
     */
    public function delete($uuid)
    {
        $item = $this->contractRepository->findOneBy(['uuid' => $uuid]);
        if (!$item) {
            return $this->json(['message' => 'Not found'], 404);
        }
        $this->contractManager->delete($item);
        return $this->json(['message' => 'Deleted successfully'], 200);
    }

    /**
     * @Route("/{uuid}/validate", name="validate_contract", methods={"POST", "PUT"},
     * options={"description"="Valider un contrat", "permission"="CONTRACT:VALIDATE"})
     */
    public function validateContract(Request $request, $uuid)
    {
    // To be implemented
    }

    /**
     * @Route("/{uuid}/pdf", name="pdf_contract", methods={"GET"},
     * options={"description"="Générer PDF d'un contrat", "permission"="CONTRACT:PDF"})
     */
    public function generatePdf($uuid)
    {
    // To be implemented
    }

    /**
     * @Route("/late", name="contract_late_list", methods={"GET"}, 
     * options={"description"="Afficher le suivi des retards", "permission"="CONTRACT:LATE:LIST"})
     */
    public function getLateList(Request $request)
    {
        return $this->json(['data' => []], 200);
    }
}