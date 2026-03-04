<?php

namespace App\Controller\Admin;

use App\Helpers\JsonHelper;
use App\Exception\ExceptionApi;
use App\Helpers\AgencyHelper;
use App\Manager\Extra\RoleManager;
use App\Repository\Extra\RoleRepository;
use App\Utils\TypeVariable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route(path="/api/private/admin/role")
 */
class RoleController extends AbstractController
{
    private $roleRepository;
    private $roleManager;
    public function __construct(
        RoleRepository $roleRepository,
        RoleManager $roleManager
    ){
        $this->roleRepository = $roleRepository;
        $this->roleManager = $roleManager;
    }

    /**
     * @Route("/", name="index_role_admin", methods={"GET"}, 
     * options={"description"="Liste des roles admin", "permission"="ROLE:ADMIN:LIST"})
     */
    public function index()
    {
        $roles = $this->roleRepository->findBy(["agency" => null], ['createdAt' => 'DESC']);
        return $this->json($roles, 200, [], ['groups' => ['role']]);
    }
    
    /**
     * @Route("/show", name="role_show_admin", methods={"GET"},
     * options={"description"="Details d'un role admin", "permission"="ROLE:ADMIN:SHOW"})
     */
    public function show(Request $request)
    {
        $data = \json_decode(json_encode($request->query->all()));
        try {
            if (isset($data->uuid) && TypeVariable::is_not_null($data->uuid))
                $role = $this->roleRepository->findOneByUuid($data->uuid);
            if (!$role) {
                $response = (new JsonHelper($role, 'Permission introuvable', 'success', 422, []))->serialize();
                return $this->json($response, Response::HTTP_UNPROCESSABLE_ENTITY, [], ['groups' => ['role']]);
            } else {
                $response = (new JsonHelper($role, null, 'success', 200, []))->serialize();
            }
            return $this->json($response, 200, [], ['groups' => ['role']]);
        } catch (ExceptionApi $e) {
            $response = (new JsonHelper(null, $e->getMessage(), 'bad_request', $e->getCode(), $e->getErrors()))->serialize();
            return $this->json($response, $e->getCode(), [], ['groups' => ['role']]);
        }
        return $this->json($role, 200, [], ['groups' => ['role','photo']]);
    }

    /**
     * @Route("/new", name="new_role_admin", methods={"POST"},
     * options={"description"="Ajouter un nouveau role admin", "permission"="ROLE:ADMIN:NEW"})
     */
    public function new(Request $request)
    {
        try {
            $data = json_decode($request->getContent());
            $role = $this->roleManager->create($data->uuid, AgencyHelper::agency($this->getUser()));
            $response = (new JsonHelper($role, 'Permission ' . $role->getNom() . ' ajouté avec succès', 'success', 200, []))->serialize();
        } catch (ExceptionApi $e) {
            $response = (new JsonHelper(null, $e->getMessage(), 'bad_request', $e->getCode(), $e->getErrors()))->serialize();
            return $this->json($response, $e->getCode(), [], ['groups' => ['role']]);
        }
        return $this->json($response, 200, [], ['groups' => ['role']]);
    }

    /**
     * @Route("/{uuid}/edit", name="edit_role_admin", methods={"POST"},
     * options={"description"="Modifier un role admin", "permission"="ROLE:ADMIN:EDIT"})
     */
    public function edit(Request $request, $uuid)
    {
        try {
            $data = json_decode($request->getContent());
            $role = $this->roleManager->update($data, $uuid);
            $response = (new JsonHelper($role, 'Le role ' . $role->getNom() . ' a été modifié avec succès', 'success', 200, []))->serialize();
        } catch (ExceptionApi $e) {
            $response = (new JsonHelper(null, $e->getMessage(), 'bad_request', $e->getCode(), $e->getErrors()))->serialize();
            return $this->json($response, $e->getCode(), [], ['groups' => ['role']]);
        }
        return $this->json($response, 200, [], ['groups' => ['role', 'file', 'folder']]);
    }
}