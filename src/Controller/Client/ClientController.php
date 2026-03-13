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
        $items = $this->clientRepository->findAll();
        return $this->json($items, 200, [], ['groups' => ["client"]]);
    }

    /**
     * @Route("/new", name="new_client", methods={"POST"}, 
     * options={"description"="Ajouter un nouveau client", "permission"="CLIENT:NEW"})
     */
    public function new (Request $request)
    {
    // To be implemented with ClientManager
    }

    /**
     * @Route("/dashboard", name="dashboard_client", methods={"GET"}, 
     * options={"description"="Statistiques du tableau de bord", "permission"="CLIENT:DASHBOARD"})
     */
    public function dashboard()
    {
        // To be implemented
        return $this->json([], 200);
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
        return $this->json($item, 200, [], ['groups' => ["client"]]);
    }

    /**
     * @Route("/{uuid}/edit", name="edit_client", methods={"PUT", "POST"}, 
     * options={"description"="Modifier un client", "permission"="CLIENT:EDIT"})
     */
    public function edit(Request $request, $uuid)
    {
    // To be implemented with ClientManager
    }

    /**
     * @Route("/{uuid}/delete", name="delete_client", methods={"DELETE"},
     * options={"description"="Supprimer un client", "permission"="CLIENT:DELETE"})
     */
    public function delete($uuid)
    {
    // To be implemented with ClientManager
    }
}