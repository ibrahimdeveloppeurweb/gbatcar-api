<?php

namespace App\Controller\Client;

use App\Manager\Client\ClientManager;
use App\Repository\Client\ClientRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route(path="/api/private/client")
 */
class ClientController extends AbstractController
{
    private $clientRepository;
    private $clientManager;

    public function __construct(
        ClientRepository $clientRepository,
        ClientManager $clientManager
        )
    {
        $this->clientRepository = $clientRepository;
        $this->clientManager = $clientManager;
    }

    /**
     * @Route("/", name="index_client", methods={"GET"},
     * options={"description"="Liste des client", "permission"="CLIENT:LIST"})
     */
    public function index(Request $request)
    {
        $filters = $request->query->all();
        $items = $this->clientManager->findByFilters($filters);
        return $this->json($items, 200, [], $this->getContext(["client", "contract", "payment"]));
    }

    /**
     * @Route("/dashboard", name="dashboard_client", methods={"GET"}, 
     * options={"description"="Statistiques du tableau de bord", "permission"="CLIENT:DASHBOARD"})
     */
    public function dashboard()
    {
        $data = $this->clientManager->getDashboardData();
        return $this->json($data, 200, [], $this->getContext(["client", "contract", "payment"]));
    }

    /**
     * @Route("/new", name="new_client", methods={"POST"}, 
     * options={"description"="Ajouter un nouveau client", "permission"="CLIENT:NEW"})
     */
    public function new (Request $request)
    {
        $raw = $request->getContent();
        $data = $raw ? json_decode($raw) : (object)$request->request->all();
        if (!$data)
            $data = new \stdClass();

        try {
            $client = $this->clientManager->create($data, $request);
            return $this->json($client, 201, [], $this->getContext());
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la création.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/{uuid}/show", name="show_client", methods={"GET"}, 
     * options={"description"="Détails d'un client", "permission"="CLIENT:SHOW"})
     */
    public function show($uuid)
    {
        $item = $this->clientRepository->findOneByUuid($uuid);
        if (!$item) {
            return $this->json(['message' => 'Not found'], 404);
        }
        return $this->json($item, 200, [], $this->getContext());
    }

    /**
     * @Route("/{uuid}/edit", name="edit_client", methods={"PUT", "POST"}, 
     * options={"description"="Modifier un client", "permission"="CLIENT:EDIT"})
     */
    public function edit(Request $request, $uuid)
    {
        $raw = $request->getContent();
        $data = $raw ? json_decode($raw) : (object)$request->request->all();
        if (!$data)
            $data = new \stdClass();

        try {
            $client = $this->clientManager->update($uuid, $data, $request);
            return $this->json($client, 200, [], ['groups' => ["client"]]);
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la modification.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/{uuid}/delete", name="delete_client", methods={"DELETE"},
     * options={"description"="Supprimer un client", "permission"="CLIENT:DELETE"})
     */
    public function delete($uuid)
    {
        $client = $this->clientRepository->findOneByUuid($uuid);
        if (!$client) {
            return $this->json(['message' => 'Client introuvable.'], 404);
        }

        try {
            $this->clientManager->delete($client);
            return $this->json(['message' => 'Client supprimé avec succès.'], 200);
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la suppression.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/{uuid}/validate", name="validate_client", methods={"POST", "PUT"},
     * options={"description"="Valider un client", "permission"="CLIENT:VALIDATE"})
     */
    public function validateClient(Request $request, $uuid)
    {
        $client = $this->clientRepository->findOneByUuid($uuid);
        if (!$client) {
            return $this->json(['message' => 'Client introuvable.'], 404);
        }

        try {
            $this->clientManager->validate($client);
            return $this->json($client, 200, [], $this->getContext());
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la validation : ' . $e->getMessage()], 500);
        }
    }

    private function getContext(array $groups = ["client"]): array
    {
        return [
            'groups' => $groups,
            'circular_reference_handler' => function ($object) {
            return method_exists($object, 'getId') ? $object->getId() : null;
        }
        ];
    }
}