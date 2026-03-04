<?php

namespace App\Manager\Admin;

use App\Entity\Extra\Path;
use App\Exception\ExceptionApi;
use App\Repository\Admin\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Extra\PathRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PathManager
{
    private $em;
    /** @var User */
    private $user;
    private $userRepository;
    private $pathRepository;
    public function __construct(
        EntityManagerInterface $em,
        UserRepository $userRepository,
        PathRepository $pathRepository,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->em = $em;
        $this->pathRepository = $pathRepository;
        $this->userRepository = $userRepository;
        if ($tokenStorage->getToken()) {
            $this->user = $tokenStorage->getToken()->getUser();
        }
    }

    public function create($data) {
        $check = $this->pathRepository->findOneByNom($data->uuid);
        if (!$check) {
            $path = new Path();
            $path
                ->setType($data->type)
                ->setNom($data->nom)
                ->setChemin($data->chemin)
                ->setLibelle($data->libelle)
                ->setPermission($data->permission)
            ;
            $this->em->persist($path);
            $this->em->flush();
            return $path;
        } else {
            throw new ExceptionApi('Cette route existe déja',
                ['msg' => 'Cette route existe déja'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
    
    public function update($path, $data) {
        $path = $this->pathRepository->findOneByUuid($path);
        $path
            ->setType($data->type)
            ->setNom($data->nom)
            ->setChemin($data->chemin)
            ->setLibelle($data->libelle)
            ->setPermission($data->permission)
        ;
        ; 
        $this->em->persist($path);   
        $this->em->flush();
        return $path;
    }

    public function delete($path)
    {
        $path = $this->pathRepository->findOneByUuid($path);
        $this->em->remove($path);
        $this->em->flush();
        return $path;
    }
}