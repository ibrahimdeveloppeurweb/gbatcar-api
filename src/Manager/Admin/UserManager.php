<?php

namespace App\Manager\Admin;

use App\Entity\Admin\User;
use App\Entity\Admin\Admin;
use App\Exception\ExceptionApi;
use App\Manager\Extra\RoleManager;
use App\Manager\Extra\UploaderManager;
use App\Repository\Extra\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Admin\UserRepository;
use App\Manager\Security\SecurityManager;
use App\Repository\Extra\FileRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Utils\Constants;
use App\Utils\FonctionUtil;
use App\Entity\Client\Client;

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

    public function __construct(

        RoleManager $roleManager,
        SecurityManager $security,
        EntityManagerInterface $em,
        FileRepository $fileRepository,
        RoleRepository $roleRepository,
        UserRepository $userRepository,
        UploaderManager $uploaderManager,

        UserPasswordEncoderInterface $passwordEncoder
        )
    {
        $this->em = $em;
        $this->security = $security;
        $this->roleManager = $roleManager;
        $this->fileRepository = $fileRepository;
        $this->roleRepository = $roleRepository;
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
        $this->uploaderManager = $uploaderManager;
    }

    /**
     * Ajouter un utilisateur
     * @param object $data
     * @throws ExceptionApi
     * @return User
     */
    public function create(object $data): User
    {

        $user = $this->checkRequirements($data);

        $admin = new Admin();
        $admin
            ->setNom($data->nom)
            ->setPrenom($data->nom);
        $this->em->persist($admin);

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

        $user->setAdmin($admin);

        $this->add($user, $data);

        $this->em->persist($user);
        $this->upload($data, $user);
        $this->em->flush();

        return $user;
    }

    /**
     * Ajout d'image de profile au compte d'un utilisateur
     * @param object $data
     * @throws ExceptionApi
     * @return User
     */
    public function img(object $data): User
    {
        $user = $this->userRepository->findOneByUuid($data->user);
        if (isset($data->files)) {
            if (count($data->files) === 0) {
                if ($user->getPhoto()) {
                    $src = $user->getPhoto()->getSrc();
                    $user->setPhoto(null);
                    $file = $this->fileRepository->findOneBySrc($src);
                    $path = __DIR__ . '/../../../public/' . $user->getFolderPath() . $src;
                    if (file_exists($path))
                        @unlink($path);
                    if ($file) {
                        $this->em->remove($file);
                    }
                }
            }
            foreach ($data->files as $item) {
                $this->uploaderManager->base64($item, $user);
            }
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
        foreach ($data->roles as $item) {
            /** @var Path $path */
            $role = $this->roleRepository->findOneByUuid($item->uuid);
            $user->addDroit($role);
        }
        $user->setPassword($this->passwordEncoder->encodePassword($user, $data->password));
        $this->add($user, $data);
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
            ->setNom(isset($data->nom) ? $data->nom : (isset($data->lastName) ? $data->lastName : null))
            ->setPrenom(isset($data->prenom) ? $data->prenom : (isset($data->firstName) ? $data->firstName : null))
            ->setTelephone($data->contact)
            ->setCivilite(isset($data->civilite) ? $data->civilite : null)
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
        if (isset($data->files)) {
            if (count($data->files) === 0) {
                if ($entity->getPhoto()) {
                    $src = $entity->getPhoto()->getSrc();
                    $entity->setPhoto(null);
                    $file = $this->fileRepository->findOneBySrc($src);
                    $path = __DIR__ . '/../../../public/' . $entity->getFolderPath() . $src;
                    if (file_exists($path))
                        @unlink($path);
                    if ($file) {
                        $this->em->remove($file);
                    }
                }
            }
            foreach ($data->files as $item) {
                $this->uploaderManager->create($item, $entity);
            }
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
    public function checkRequirements(object $data)
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

    /**
     * Création automatique d'un compte utilisateur pour un client lors de la validation du contrat
     * 
     * @param Client $client
     * @return array|null
     */
    public function createClientAccount(Client $client): ?array
    {
        if (!$client->getEmail()) {
            return null;
        }

        $username = $client->getEmail();
        $password = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT); // Code à 4 chiffres

        /** @var User $user */
        $user = $this->userRepository->findOneBy(['username' => $username]);

        if (!$user) {
            $user = new User();
            $user->setUsername($username)
                ->setEmail($username)
                ->setType(Constants::USER_TYPE['CLIENT'])
                ->setIsEnabled(true)
                ->setIsFirst(false);

        // Assign ROLE_CLIENT
        // $role = $this->roleRepository->findOneBy(['libelle' => Constants::USER_ROLES['CLIENT']]);
        // if ($role) {
        //     $user->addDroit($role);
        // }
        }

        $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
        $user->setNom($client->getLastName())
            ->setPrenom($client->getFirstName())
            ->setTelephone($client->getPhone())
            ->setCivilite($client->getCivilite());

        $this->em->persist($user);
        $this->em->flush();

        return [
            'username' => $username,
            'password' => $password
        ];
    }
}