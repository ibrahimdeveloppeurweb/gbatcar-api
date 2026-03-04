<?php

namespace App\Manager\Admin;

use App\Sms\SecuritySms;
use App\Entity\Admin\User;
use App\Entity\Admin\Admin;
use App\Entity\Admin\Agency;
use App\Entity\Admin\Service;
use App\Exception\ExceptionApi;
use App\Mailing\SecurityMailing;
use App\Manager\Extra\RoleManager;
use App\Manager\Api\ApiVeriffManager;
use App\Manager\Extra\UploaderManager;
use App\Repository\Extra\RoleRepository;
use App\Manager\Client\ContraintManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Admin\UserRepository;
use App\Manager\Security\SecurityManager;
use App\Repository\Admin\ServiceRepository;
use App\Repository\Extra\FileRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserManager
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    private $security;
    private $securitySms;
    private $roleManager;
    private $userRepository;
    private $fileRepository;
    private $roleRepository;
    private $securityMailing;
    private $passwordEncoder;
    private $uploaderManager;
    private $apiVeriffManager;
    private $contraintManager;
    private $serviceRepository;
    public function __construct(
       
        RoleManager $roleManager,
        SecurityManager $security,
        EntityManagerInterface $em,
        FileRepository $fileRepository,
        RoleRepository $roleRepository,
        UserRepository $userRepository,
        UploaderManager $uploaderManager,
        ServiceRepository $serviceRepository,
       
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $this->em = $em;
        $this->security = $security;
        $this->roleManager = $roleManager;
        $this->fileRepository = $fileRepository;
        $this->roleRepository = $roleRepository;
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
        $this->uploaderManager = $uploaderManager;
        $this->serviceRepository = $serviceRepository;
       
    }

    /**
     * Ajouter un utilisateur
     * @param object $data
     * @param Agency|null $agency
     * @throws ExceptionApi
     * @return User
     */
    public function create(object $data, ?Agency $agency): User
    {

        $user = $this->checkRequirements($data, $agency);
        $service = $this->serviceRepository->findOneByUuid(isset($data->service) ? $data->service : null);
        if (!$agency) {
            $admin = new Admin();
            $admin
                ->setNom($data->nom)
                ->setPrenom($data->nom);
            $this->em->persist($admin);
        }
        $user = new User();
        $user
            ->setPassword($this->passwordEncoder->encodePassword($user, $data->password))
            ->setIsEnabled(true)
             ->setConfirmationToken($this->security->getToken())
            ->setIsFirst(false);
        foreach ($data->roles as $item) {
            $role = $this->roleRepository->findOneByUuid($item->uuid);
            $user->addDroit($role);
        }
        if ($agency) {
            $user->setAgency($agency);
        } else {
            $user->setAdmin($admin);
        }
        $this->add($user, $data, $service);

        $this->em->persist($user);
        $this->upload($data, $user);
        $this->em->flush();

        // //Envoi de mail/SMS
        // $this->securityMailing->userCreate($user, $data->password, $agency);
        // $this->securitySms->create($user, $data->password, $agency);
        return $user;
       
    }

    /**
     * Ajout des utilisateur lies a une agence
     * @param object $data
     * @param Agency $agency
     * @param boolean $first
     * @param string $password
     * @throws ExceptionApi
     * @return User
     */
     public function createUserAgency(object $data, Agency $agency, bool $first, string $password): User
    {
        $this->contraintManager->user($agency);
        $this->checkRequirements($data, $agency);
        $serviceUuid = isset($data->service) ? $data->service : null;
        $service = $this->serviceRepository->findOneByUuid($serviceUuid);

        $user = new User();
        $this->addAgency($user, $data);
        $user
            ->setIsFirst($first)
            ->setType($first === true ? User::TYPE['ADMIN'] : User::TYPE['USER'])
            ->setAgency($agency)
            // ->setService($service)
            ->setNom($data->nom)
            ->setCivilite(isset($data->civilite) ? $data->civilite : null)
            ->setTelephone(isset($data->contact) ? $data->contact : null)
            ->setPrenom($first ? $data->nom : null)
            ->setPassword($this->passwordEncoder->encodePassword($user, $password));
        if ($first === true) {
            $role = $this->roleManager->firstRole($agency);
            $user->addDroit($role);
        }
        $this->em->persist($user);
        if ($first === false) {
            $this->em->flush();
        }
        //Envoi d'SMS et mail
        // $this->securityMailing->userCreate($user, $password, $agency);
        return $user;
    }

    /**
     * Ajout dd'image de profile au compte d'un utilisateur
     * @param object $data
     * @param Agency $agency
     * @throws ExceptionApi
     * @return User
     */
    public function img(object $data, Agency $agency): User
    {
        
        $this->contraintManager->user($agency);
        $user = $this->userRepository->findOneByUuid($data->user);
        if(count($data->files) === 0 ){
            if($user->getPhoto()){
                $src = $user->getPhoto()->getSrc();
                $user->setPhoto(null);
                $file = $this->fileRepository->findOneBySrc($src);
                $path = __DIR__ . '/../../../public/' . $user->getFolderPath() . $src;
                if (file_exists($path)) 
                @unlink($path);
                $this->em->remove($file);
            }
        }
        foreach ($data->files as $item) {
            $this->uploaderManager->base64($item, $user);
        }

        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    /**
     * Modifier un utilisateur
     * @param string $uuid
     * @param object $data
     * @throws ExceptionApi
     * @return User
     */
    public function update(string $uuid, object $data): User
    {
          /** @var User $user */
        $user = $this->userRepository->findOneByUuid($uuid);
        if (!$user) {
            throw new ExceptionApi(
                'Cet utilisateur est introuvable.',
                ['msg' => 'Cet utilisateur est introuvable.'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        $service = $this->serviceRepository->findOneByUuid($data->service);
        foreach ($user->getDroits() as $key => $value) {
            $uuidR[] = !empty($data->roles[$key]->uuid) ? $data->roles[$key]->uuid : null;
            if ($uuidR !== null) {
                if (!in_array($value->getUuid(), $uuidR)) {
                    $user->removeDroit($value);
                }
            } else {
                $user->removeDroit($value);
            }
        }
        foreach ($data->roles as $item) {
            /** @var Path $path */
            $role = $this->roleRepository->findOneByUuid($item->uuid);
            $user->addDroit($role);
        }
        $this->add($user, $data, $service);
        $this->em->persist($user);
        $this->upload($data, $user);
        $this->em->flush();
        return $user;
    }

    /**
     * Supprimer un utilisateur
     * @param User $user
     * @return User
     */
    public function delete(User $user): User
    {
         /** @var User $user */
        $user = $this->userRepository->findOneByUuid($user);
        if (!$user) {
            throw new ExceptionApi(
                'Cet utilisateur est introuvable.',
                ['msg' => 'Cet utilisateur est introuvable.'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        $this->em->remove($user);
        $this->em->flush();
        return $user;
    }   


    /**
     * Add function
     * @param User $user
     * @param object $data
     * @return void
     */
    public function add(User $user, object $data)
    {
         /** @var User $user */
        $user
            ->setNom($data->nom)
            ->setTelephone($data->contact)
            ->setCivilite(isset($data->civilite) ? $data->civilite : null)
            // ->setService($service)
            ->setEmail($data->email)
            ->setUsername($data->username);
    }

    /**
     * Upload de fichier
     * @param object $data
     * @param [type] $entity
     * @return void
     */
    public function upload(object $data, $entity)
    {
        if(count($data->files) === 0 ){
            if($entity->getPhoto()){
                $src = $entity->getPhoto()->getSrc();
                $entity->setPhoto(null);
                $file = $this->fileRepository->findOneBySrc($src);
                $path = __DIR__ . '/../../../public/' . $entity->getFolderPath() . $src;
                if (file_exists($path)) 
                @unlink($path);
                $this->em->remove($file);
            }
        }
        foreach ($data->files as $item) {
            $this->uploaderManager->create($item, $entity);
        }
    }

    /**
     * Add agency funtion
     * @param User $user
     * @param object $data
     * @return void
     */
    public function addAgency(User $user, object $data)
    {
        $user
            ->setEmail($data->email)
            ->setUsername($data->email)
            ->setIsEnabled(true)
            ->setConfirmationToken($this->security->getToken());
    }

    /**
     * Validation
     * @param object $data
     * @throws ExceptionApi
     * @return void
     */
    public function checkRequirements(object $data, Agency $agency)
    {
        $email = isset($data->user) ? $data->user->email : $data->email;
        if (isset($data->username)) {
            $username = $data->username;
        }
        if (isset($data->email)) {
            $username = $data->email;
        }
        if (isset($data->user->email)) {
            $username = $data->user->email;
        }

        $checkUsername = $this->userRepository->findOneByUsername($username);
        if ($checkUsername) {
            throw new ExceptionApi(
                "Cet adresse mail est déjà associé à un compte en tant que Login utilisateur.",
                ["msg" => "Cet adresse mail est déjà associé à un compte en tant que Login utilisateur."],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

       
    }
}
