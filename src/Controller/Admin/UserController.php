<?php

namespace App\Controller\Admin;

use App\Helpers\JsonHelper;
use App\Exception\ExceptionApi;
use App\Manager\Admin\UserManager;
use App\Repository\Admin\UserRepository;
use App\Utils\TypeVariable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route(path="/api/private/admin/user")
 */
class UserController extends AbstractController
{
    private $userRepository;
    private $userManager;
    private $em;
    public function __construct(
        UserRepository $userRepository,
        UserManager $userManager,
        EntityManagerInterface $em
        )
    {
        $this->userRepository = $userRepository;
        $this->userManager = $userManager;
        $this->em = $em;
    }

    /**
     * @Route("/", name="index_user_admin", methods={"GET"},
     * options={"description"="Liste des utilisateurs admin", "permission"="USER:ADMIN:LIST"})
     */
    public function index(Request $request)
    {
        $data = \json_decode(json_encode($request->query->all()));
        if (isset($data->service) && TypeVariable::is_not_null($data->service)) {
            $users = $this->userRepository->findByService('ADMIN', $data->service, null);
            return $this->json($users, 200, [], ['groups' => ["user", "file", "photo"]]);
        }
        $users = $this->userRepository->findByAdmin();
        return $this->json($users, 200, [], ['groups' => ["user", "file", "photo"]]);
    }

    /**
     * @Route("/new", name="new_user_admin", methods={"POST"}, 
     * options={"description"="Ajouter un nouveau utilisateur", "permission"="USER:ADMIN:NEW"})
     */
    public function new (Request $request)
    {
        $data = \json_decode($request->getContent());
        try {
            $user = $this->userManager->create($data);
            return $this->json($user, 201, [], ['groups' => ["user", "file", "photo"]]);
        }
        catch (ExceptionApi $e) {
            return $this->json($e->getErrors(), $e->getCode());
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Une erreur est survenue lors de la création de l\'utilisateur.', 'details' => $e->getMessage()], 500);
        }
    }




    /**
     * @Route("/show", name="show_user_admin", methods={"GET"}, 
     * options={"description"="Détails d'un utilisateur", "permission"="USER:ADMIN:SHOW"})
     */
    public function show(Request $request)
    {
        $uuid = $request->query->get('uuid');
        $user = $this->userRepository->findOneByUuid($uuid);
        if (!$user) {
            return $this->json(['msg' => 'Utilisateur introuvable.'], 404);
        }
        return $this->json($user, 200, [], ['groups' => ["user", "file", "photo", "role"]]);
    }

    /**
     * @Route("/{uuid}/edit", name="edit_user_admin", methods={"POST"}, 
     * options={"description"="Modifier un utilisateur", "permission"="USER:ADMIN:EDIT"})
     */
    public function edit(Request $request, $uuid)
    {
        $data = \json_decode($request->getContent());

        try {
            $user = $this->userManager->update($uuid, $data);
            return $this->json($user, 200, [], ['groups' => ["user", "file", "photo"]]);
        }
        catch (ExceptionApi $e) {
            return $this->json($e->getErrors(), $e->getCode());
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Une erreur est survenue lors de la modification de l\'utilisateur.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/{uuid}/delete", name="delete_user", methods={"DELETE"},
     * options={"description"="Supprimer un utilisateur", "permission"="USER:ADMIN:DELETE"})
     */
    public function delete($uuid)
    {
        try {
            $user = $this->userRepository->findOneByUuid($uuid);
            if (!$user) {
                return $this->json(['msg' => 'Utilisateur introuvable.'], 404);
            }
            $this->userManager->delete($user);
            return $this->json(['message' => 'Utilisateur supprimé avec succès.'], 200);
        }
        catch (ExceptionApi $e) {
            return $this->json($e->getErrors(), $e->getCode());
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Une erreur est survenue lors de la suppression de l\'utilisateur.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/{uuid}/toggle", name="toggle_user_admin", methods={"PATCH"},
     * options={"description"="Activer/Désactiver un utilisateur", "permission"="USER:ADMIN:EDIT"})
     */
    public function toggle($uuid)
    {
        try {
            $user = $this->userRepository->findOneByUuid($uuid);
            if (!$user) {
                return $this->json(['msg' => 'Utilisateur introuvable.'], 404);
            }
            $user->setIsEnabled(!$user->isEnabled());
            $this->em->persist($user);
            $this->em->flush();
            $status = $user->isEnabled() ? 'activé' : 'désactivé';
            return $this->json([
                'message' => "Le compte a été {$status} avec succès.",
                'isEnabled' => $user->isEnabled()
            ], 200);
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors du changement de statut.', 'details' => $e->getMessage()], 500);
        }
    }
}