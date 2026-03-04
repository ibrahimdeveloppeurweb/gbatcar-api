<?php

namespace App\Manager\Admin;

use App\Entity\Admin\User;
use App\Entity\Admin\Agency;
use App\Entity\Admin\Service;
use App\Exception\ExceptionApi;
use App\Repository\Admin\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Admin\ServiceRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ServiceManager
{
    /** @var User */
    private $user;
    private $em;
    private $serviceRepository;
    private $userRepository;
    public function __construct(
        EntityManagerInterface $em,
        UserRepository $userRepository,
        TokenStorageInterface $tokenStorage,
        ServiceRepository $serviceRepository
    ) {
        $this->em = $em;
        $this->serviceRepository = $serviceRepository;
        $this->userRepository = $userRepository;
        if ($tokenStorage->getToken()) {
            $this->user = $tokenStorage->getToken()->getUser();
        }
    }

    /**
     * Ajouter un service
     * @param object $data
     * @param Agency $agency
     * @throws ExceptionApi
     * @return Service
     */
    public function create(object $data, Agency $agency = null): Service
    {
        $this->checkRequirements($data, $agency);

        $service = new Service();
        $this->add($service, $data, $agency);
        $this->em->flush();
        return $service;
    }

    /**
     * Modifier un service
     * @param object $data
     * @param string $uuid
     * @throws ExceptionApi
     * @return Service
     */
    public function update(object $data, string $uuid): Service
    {
        /** @var Service $service */
        $service = $this->serviceRepository->findOneByUuid($uuid);
        if (!$service) {
            throw new ExceptionApi('Le service est introuvable.',
                ['msg' => 'Le service est introuvable.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $this->add($service, $data, $service->getAgency());
        $this->em->flush();
        return $service;
    }

    /**
     * Supprimer un service
     * @param string $uuid
     * @throws ExceptionApi
     * @return void
     */
    public function delete(string $uuid)
    {
        /** @var Service $service */
        $service = $this->serviceRepository->findOneByUuid($uuid);
        if (!$service) {
            throw new ExceptionApi('Le service est introuvable.',
                ['msg' => 'Le service est introuvable.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $user = $this->userRepository->findBy(['service' => $service]);
        if ($user) {
            throw new ExceptionApi('Vous ne pouvez pas supprimer car ce service a déja éte attribué',
                ['msg' => 'Vous ne pouvez pas supprimer car ce service a déja éte attribué'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $this->em->remove($service);
        $this->em->flush();
    }

    /**
     * Function Add 
     * @param Service $service
     * @param object $data
     * @param Agency $agency
     * @return void
     */
    public function add(Service $service, object $data, Agency $agency)
    {
        /** @var User $responsable */
        $responsable = $this->userRepository->findOneByUuid($data->responsable);
        $service
            ->setNom($data->nom)
            ->setDirection($data->direction)
            ->setDescription($data->description)
            ->setResponsable($responsable)
            ->setAgency($agency)
        ;
        $this->em->persist($service);
    }

    /**
     * Validation
     * @param object $data
     * @param Agency|null $agency
     * @throws ExceptionApi
     * @return void
     */
    public function checkRequirements(object $data, ?Agency $agency)
    { 
        $responsable = $this->userRepository->findOneByUuid($data->responsable);
        if (!$responsable) {
            throw new ExceptionApi('Le responsable désigné est introuvable.',
                ['msg' => 'Le responsable désigné est introuvable.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        if ($agency) {
            $checkC = $this->serviceRepository->findOneBy(['agency' => $agency, 'nom' => $data->nom]);
            if ($checkC) {
                throw new ExceptionApi('Cet service existe déja',
                ['msg' => 'Cet service existe déja'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        } else {
            $checkA = $this->serviceRepository->findOneBy(['agency' => null, 'nom' => $data->nom]);
            if ($checkA) {
                throw new ExceptionApi('Cet service existe déja.',
                ['msg' => 'Cet service existe déja.'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }
        if (!$this->user) {
            throw new ExceptionApi("Vous n'avez pas accès à cette ressource.",
                ['msg' => "Vous n'avez pas accès à cette ressource."], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}