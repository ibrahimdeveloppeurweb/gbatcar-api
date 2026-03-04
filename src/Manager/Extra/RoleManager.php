<?php

namespace App\Manager\Extra;

use App\Entity\Admin\User;
use App\Entity\Extra\Role;
use App\Helpers\RouteHelper;
use App\Exception\ExceptionApi;
use App\Repository\Admin\UserRepository;
use App\Repository\Extra\PathRepository as ExtraPathRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Extra\RoleRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RoleManager
{
    /** @var User */
    private $user;
    private $em;
    private $roleRepository;
    private $pathRepository;
    private $userRepository;
    public function __construct(
        EntityManagerInterface $em,
        ExtraPathRepository $pathRepository,
        UserRepository $userRepository,
        TokenStorageInterface $tokenStorage,
        RoleRepository $roleRepository
    )
    {
        $this->em = $em;
        $this->pathRepository = $pathRepository;
        $this->roleRepository = $roleRepository;
        $this->userRepository = $userRepository;
        if ($tokenStorage->getToken()) {
            $this->user = $tokenStorage->getToken()->getUser();
        }
    }

    public function create($data) {
        $agency = $this->user->getAgency() !== null ? $this->user->getAgency() : null;
        /** @var Role $check */
        $check = $this->roleRepository->findOneBy(["nom" => $data->nom, "agency" => $agency]);
        if (!$check) {
            $role = new Role(); 
            $this->add($data->nom, $data->description, $role, $agency);
            foreach ($data->paths as $item) {
                $path = $this->pathRepository->findOneByUuid($item->uuid);
                $role->addPath($path);
            }
            $this->em->persist($role);
            $this->em->flush();
            return $role;
        } else {
            throw new ExceptionApi('Cette Permission existe déja',
                ['msg' => 'Cette Permission existe déja'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
    
    public function update($data, $uuid) {
        /** @var Role $path*/
        $role = $this->roleRepository->findOneByUuid($uuid);
        foreach ($role->getPaths() as $key => $value) {
            $uuidO[] = !empty($data->paths[$key]->uuid) ? $data->paths[$key]->uuid : null;
            if ($uuidO !== null) {
                if (!in_array($value->getUuid(), $uuidO)) {
                    $role->removePath($value); 
                }
            } else { $role->removePath($value); }
        } 
        
        foreach ($data->paths as $path) {
            /** @var Path $path*/
            $path = $this->pathRepository->findOneByUuid($path->uuid); 
            $role->addPath($path);
        } 
        $this->add($data->nom, $data->description, $role, $role->getAgency());
        $this->em->persist($role);
        $this->em->flush();
        return $role;
    }

    public function delete($uuid)
    {
        $role = $this->roleRepository->findOneByUuid($uuid);
        $user = $this->userRepository->findBy(['role' => $role]);
        if ($user) {
            throw new ExceptionApi('Vous ne pouvez pas supprimer car ce role a déja éte attribué',
                ['msg' => 'Vous ne pouvez pas supprimer car ce role a déja éte attribué'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $this->em->remove($role);
        $this->em->flush();
        return $role;
    }

    public function add($nom, $description, $role, $agency)
    {
        $role
            ->setNom($nom)
            ->setDescription($description)
            ->setAgency($agency !== null ? $agency : null)
        ;  
        return $role;
    }

    public function firstRole($agency)
    {
        $pathsRow = $this->pathRepository->findAll();

        // Role principale de la gestion locative
        $tenant = new Role();
        $this->add('Gestion locatives', 'Gestion locatives', $tenant, $agency);
        $paths = RouteHelper::TENANT_ROUTE($pathsRow);
        foreach ($paths as $path) {
            $tenant->addPath($path);
        }
        $this->em->persist($tenant);
        
        // Role restreint de la gestion locative
        $tenantRestricted = new Role();
        $this->add('Gestion locatives restreint', 'Gestion locatives restreint', $tenantRestricted, $agency);
        $paths = RouteHelper::TENANT_RESTRICTED_ROUTE($pathsRow);
        foreach ($paths as $path) {
            $tenantRestricted->addPath($path);
        }
        $this->em->persist($tenantRestricted);

        // Role principale de la gestion tresorerie
        $treasury = new Role();
        $this->add('Gestion tresorerie', 'Gestion tresorerie', $treasury, $agency);
        $paths = RouteHelper::TREASURY_ROUTE($pathsRow);
        foreach ($paths as $path) {
            $treasury->addPath($path);
        }
        $this->em->persist($treasury);

        // Role restreint de la gestion tresorerie
        $treasuryRestricted = new Role();
        $this->add('Gestion tresorerie restreint', 'Gestion tresorerie restreint', $treasuryRestricted, $agency);
        $paths = RouteHelper::TREASURY_RESTRICTED_ROUTE($pathsRow);
        foreach ($paths as $path) {
            $treasuryRestricted->addPath($path);
        }
        $this->em->persist($treasuryRestricted);

        // Role principale de la gestion patrimoine
        $patrimoine = new Role();
        $this->add('Gestion patrimoine', 'Gestion patrimoine', $patrimoine, $agency);
        $paths = RouteHelper::PATRIMOINE_ROUTE($pathsRow);
        foreach ($paths as $path) {
            $patrimoine->addPath($path);
        }
        $this->em->persist($patrimoine);

        // Role restreint de la gestion patrimoine
        $patrimoineRestricted = new Role();
        $this->add('Gestion patrimoine restreint', 'Gestion patrimoine restreint', $patrimoineRestricted, $agency);
        $paths = RouteHelper::PATRIMOINE_RESTRICTED_ROUTE($pathsRow);
        foreach ($paths as $path) {
            $patrimoineRestricted->addPath($path);
        }
        $this->em->persist($patrimoineRestricted);

        // Role principale: Gestion d'acces à toute les route
        $admin = new Role();
        $this->add('Gestion d\'acces à toute les route', 'Gestion d\'acces à toute les route', $admin, $agency);
        $paths = $pathsRow;
        foreach ($paths as $path) {
            $admin
                ->addPath($path) 
                ->setIsAdmin(true) 
            ;
        }
        $this->em->persist($admin);

        return $admin;
    }
}