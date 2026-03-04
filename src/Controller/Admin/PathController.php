<?php

namespace App\Controller\Admin;

use App\Entity\Extra\Path;
use App\Helpers\JsonHelper;
use App\Exception\ExceptionApi;
use App\Manager\Admin\PathManager;
use App\Repository\Extra\PathRepository;
use App\Utils\TypeVariable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route(path="/api/private/admin/path")
 */
class PathController extends AbstractController
{
    private $pathRepository;
    private $pathManager;
    public function __construct(
        PathRepository $pathRepository,
        PathManager $pathManager
    ) {
        $this->pathRepository = $pathRepository;
        $this->pathManager = $pathManager;
    }

    /**
     * @Route("/", name="index_path", methods={"GET"}, 
     * options={"description"="Liste des routes", "permission"="PATH:LIST"})
     */
    public function index(Request $request)
    {
        $data = \json_decode(json_encode($request->query->all()));
        if (isset($data->type) && TypeVariable::is_not_null($data->type)) {
            $paths = $this->pathRepository->findBy(['type' => Path::TYPE[$data->type]]);
            return $this->json($paths, 200, [], ['groups' => ['path']]);
        }
        $paths = $this->pathRepository->findByAll();
        return $this->json($paths, 200, [], ['groups' => ['path']]);
    }

    /**
     * @Route("/new", name="new_path", methods={"POST"}, 
     * options={"description"="Ajouter une route", "permission"="PATH:NEW"})
     */
    public function new(Request $request)
    {
        try {
            $data = \json_decode($request->getContent());
            $path = $this->pathManager->create($data);
            $response = (new JsonHelper($path, 'Route ' . $path->getNom() . ' ajouter avec succès','success', 200, []))->serialize();
        } catch (ExceptionApi $e) { 
            $response = (new JsonHelper(null, $e->getMessage(),'bad_request', $e->getCode(), $e->getErrors()))->serialize();
            return $this->json($response, $e->getCode(), [], ['groups' => ['service']]);
        }
        return $this->json($response, 200, [], ['groups' => ['service']]);
    }

    /**
     * @Route("/{path}/edit", name="edit_path", methods={"POST"}, 
     * options={"description"="Modifier une route", "permission"="PATH:EDIT"})
     */
    public function edit(Request $request, $path)
    {
        try {
            $data = \json_decode($request->getContent());
            $path = $this->pathManager->update($path, $data);
            $response = (new JsonHelper($path, 'Route' . $path->getNom() . ' a été modifié avec succès','success', 200, []))->serialize();
        } catch (ExceptionApi $e) {
            $response = (new JsonHelper(null, $e->getMessage(),'bad_request', $e->getCode(), $e->getErrors()))->serialize();
            return $this->json($response, $e->getCode(), [], ['groups' => ['agency']]);
        }
        
        return $this->json($response, 200, [], ['groups' => ['agency']]);
    }

    /**
     * @Route("/{path}/delete", name="delete_path", methods={"DELETE"},
     * options={"description"="Supprimer une route", "permission"="PATH:DELETE"})
     */
    public function delete($path)
    {
        try {
            $path = $this->pathManager->delete($path);
            $response = (new JsonHelper($path, 'Route '. $path->getNom() .' a été supprimé avec succès', 'success', 200, []))->serialize();
        } catch (ExceptionApi $e) {
            $response = (new JsonHelper(null, $e->getMessage(), 'bad_request', $e->getCode(), $e->getErrors()))->serialize();
            return $this->json($response, $e->getCode(), [], ['groups' => ['service']]);
        }
        return $this->json($response, 200, [], ['groups' => ['service']]);
    }
}