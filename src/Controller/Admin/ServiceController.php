<?php

namespace App\Controller\Admin;

use App\Helpers\JsonHelper;
use App\Exception\ExceptionApi;
use App\Manager\Admin\ServiceManager;
use App\Repository\Admin\ServiceRepository;
use App\Utils\TypeVariable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route(path="/api/private/admin/service")
 */
class ServiceController extends AbstractController
{
    private $serviceRepository;
    private $serviceManager;
    public function __construct(
        ServiceRepository $serviceRepository,
        ServiceManager $serviceManager
    ) {
        $this->serviceRepository = $serviceRepository;
        $this->serviceManager = $serviceManager;
    }

    /**
     * @Route("/", name="admin_index_service", methods={"GET"}, 
     * options={"description"="Liste des services de la platform admin", "permission"="SERVICE:ADMIN:LIST"})
     */
    public function index()
    {
        $services = $this->serviceRepository->findBy(["agency" => null], ['createdAt' => 'DESC'], 20);
        return $this->json($services, 200, [], ['groups' => ['service']]);
    }
    
    /**
     * @Route("/show", name="admin_service_show", methods={"GET"},
     * options={"description"="Details d'un service de la platform admin", "permission"="SERVICE:ADMIN:SHOW"})
     */
    public function show(Request $request)
    {
        $data = \json_decode(json_encode($request->query->all()));
        try {
            if (isset($data->uuid) && TypeVariable::is_not_null($data->uuid))
                $service = $this->serviceRepository->findOneByUuid($data->uuid);
            if (!$service) {
                $response = (new JsonHelper($service, 'Service introuvable', 'success', 422, []))->serialize();
                return $this->json($response, Response::HTTP_UNPROCESSABLE_ENTITY, [], ['groups' => ['service']]);
            } else {
                $response = (new JsonHelper($service, null, 'success', 200, []))->serialize();
            }
            return $this->json($response, 200, [], ['groups' => ['service']]);
        } catch (ExceptionApi $e) {
            $response = (new JsonHelper(null, $e->getMessage(), 'bad_request', $e->getCode(), $e->getErrors()))->serialize();
            return $this->json($response, $e->getCode(), [], ['groups' => ['service']]);
        }
        return $this->json($service, 200, [], ['groups' => ['service','photo']]);
    }

    /**
     * @Route("/new", name="admin_new_service", methods={"POST"},
     * options={"description"="Ajouter un service de la platform admin", "permission"="SERVICE:ADMIN:NEW"})
     */
    public function new(Request $request)
    {
        try {
            $data = \json_decode($request->getContent());
            $service = $this->serviceManager->create($data);
            $response = (new JsonHelper($service, 'Service ' . $service->getNom() . ' ajouter avec succès','success', 200, []))->serialize();
        } catch (ExceptionApi $e) { 
            $response = (new JsonHelper(null, $e->getMessage(),'bad_request', $e->getCode(), $e->getErrors()))->serialize();
            return $this->json($response, $e->getCode(), [], ['groups' => ['service']]);
        }
        return $this->json($response, 200, [], ['groups' => ['service']]);
    }

    /**
     * @Route("/{service}/edit", name="admin_edit_service", methods={"POST"}, 
     * options={"description"="Modifier une service de la platform admin", "permission"="SERVICE:ADMIN:EDIT"})
     */
    public function edit(Request $request, $service)
    {
        try {
            $data = \json_decode($request->getContent());
            $service = $this->serviceManager->update($data, $service);
            $response = (new JsonHelper($service, 'Service ' . $service->getNom() . ' modifié avec succès','success', 200, []))->serialize();
        } catch (ExceptionApi $e) {
            $response = (new JsonHelper(null, $e->getMessage(),'bad_request', $e->getCode(), $e->getErrors()))->serialize();
            return $this->json($response, $e->getCode(), [], ['groups' => ['service']]);
        }
        return $this->json($response, 200, [], ['groups' => ['service']]);
    }

    /**
     * @Route("/{service}/delete", name="admin_delete_service", methods={"DELETE"},
     * options={"description"="Supprimer un service de la platform admin", "permission"="SERVICE:ADMIN:DELETE"})
     */
    public function delete($service)
    {
        try {
            $service = $this->serviceManager->delete($service);
            $response = (new JsonHelper($service, 'Service a été supprimé avec succès', 'success', 200, []))->serialize();
        } catch (ExceptionApi $e) {
            $response = (new JsonHelper(null, $e->getMessage(), 'bad_request', $e->getCode(), $e->getErrors()))->serialize();
            return $this->json($response, $e->getCode(), [], ['groups' => ['service']]);
        }
        return $this->json($response, 200, [], ['groups' => ['service']]);
    }
}