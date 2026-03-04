<?php

namespace App\Manager\Security;

use App\Utils\Fonctions;
use App\Utils\Constants;
use App\Entity\Admin\User;
use App\Utils\TypeVariable;
use App\Security\JwtEncoder;
use App\Exception\ExceptionApi;
use App\Model\User as UserModel;
use App\Entity\Extra\RefreshToken;
use App\Repository\Admin\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\Extra\RefreshTokenRepository;
use App\Utils\FonctionUtil;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;

class SecurityManager
{
    private $em;
    /** @var User **/
    private $user;
    private $roles;
    private $encoder;
    private $username;
    private $jwtEncoder;
    private $isFirstUser;
    private $securitySms;
    private $refreshToken;
    private $userRepository;
    private $securityMailing;
    private $passwordEncoder;
    private $ownerRepository;
    private $tenantRepository;
    private $settingRepository;
    private $customerRepository;
    private $refreshTokenRepository;
    public function __construct(
        JwtEncoder $encoder,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        JWTEncoderInterface $jwtEncoder,
        TokenStorageInterface $tokenStorage,
        UserPasswordEncoderInterface $passwordEncoder,
        RefreshTokenRepository $refreshTokenRepository
    ) {
        $this->em = $em;
        $this->encoder = $encoder;
        $this->jwtEncoder = $jwtEncoder;
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
        $this->refreshTokenRepository = $refreshTokenRepository;
        if ($tokenStorage->getToken()) {
            $this->user = $tokenStorage->getToken()->getUser();
        }
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws ExceptionApi
     */
    public function checkCredential(Request $request)
    {
        $body = json_decode($request->getContent(), true);
       
        if (isset($body['type']) && !$this->isPlateform($body['type'])) {
            throw new ExceptionApi(
                'Accès refusé',
                ['msg' => "Vous êtes pas autorisé à avoir accès à cette plateforme."],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if (!isset($body['username']) && !isset($body['password'])) {
            throw new ExceptionApi(
                "L'email et le mot de passe sont obligatoire.",
                ['msg' => "L'email et le mot de passe sont obligatoire."],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if (!isset($body['username'])) {
            throw new ExceptionApi(
                "L'email est obligatoire",
                ['msg' => "L'email est obligatoire."],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        $username = $body['username'];
        if (!isset($body['password'])) {
            throw new ExceptionApi(
                'Le password est obligatoire.',
                ['msg' => 'Le password est obligatoire.'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        $password = $body['password'];
      
        try {
            $user = $this->userRepository->findOneBy(['username' => $username]);
            
        } catch (NonUniqueResultException $e) {
            $user = null;
        }
 
        if (!$user) {
            throw new ExceptionApi(
                'Cet utilisateur est introuvable',
                ['msg' => "Cet utilisateur est introuvable"],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    
        $isValid = $this->passwordEncoder->isPasswordValid($user, $password);
        if (!$isValid) {
            throw new ExceptionApi(
                'Accès incorrectes.',
                ['msg' => ['Le mot de passe est incorrect.']],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
     
        if ($user->isLocked()) {
            throw new ExceptionApi(
                'Accès refusé',
                ['msg' => ["Le compte de cet utilisateur n'est pas active."]],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
            //Envoi de mail
          //  $this->securityMailing->disabled($user);
          //  $this->securitySms->disabled($user);
        }
        if (!$user->isEnabled()) {
            throw new ExceptionApi(
                'Accès refusé',
                ['msg' => ["Le compte de cet utilisateur n'est pas validé."]],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
           
            //Envoi de mail
           // $this->securityMailing->enabled($user);
           // $this->securitySms->enabled($user);
        }
      
        //On verifie les autorisation d'acces ADMIN
        if ($this->isPlateform($body['type']) && $body['type'] === 'ADMIN' && Constants::USER_ROLES[$body['type']] !== $user->getRoles()[0]) {
            throw new ExceptionApi(
                'Accès refusé',
                ['msg' => ["Cet utilisateur n'a pas accès à cette plateforme."]],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        //On verifie les autorisation d'acces AGENCY
        if ($this->isPlateform($body['type']) && $body['type'] === 'AGENCY' && Constants::USER_ROLES[$body['type']] !== $user->getRoles()[0]) {
            throw new ExceptionApi(
                'Accès refusé',
                ['msg' => ["Cet utilisateur n'a pas accès à cette plateforme."]],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
      
        //On verifie les autorisation d'acces MOBILE
        if (
            $this->isPlateform($body['type']) &&
            ($body['type'] === 'MOBILE') &&
            (Constants::USER_ROLES["AGENCY"] !== $user->getRoles()[0])
        ) {
            throw new ExceptionApi(
                'Accès refusé',
                ['msg' => ["Cet utilisateur n'a pas accès à cette plateforme."]],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        //On verifie les autorisation d'acces SITE
        if (
            $this->isPlateform($body['type']) &&
            $body['type'] === 'SITE' &&
            ($user->getType() === 'ADMIN' || $user->getType() === 'USER') &&
            $this->isPlateform($body['type']) &&
            $body['type'] === 'SITE' &&
            ($user->getType() === 'ADMIN' || $user->getType() === 'USER') &&
            $user->getIsFirst() === false
        ) {
            throw new ExceptionApi(
                'Accès refusé',
                ['msg' => ["En tant qu'utilisateur, vous n'avez pas droit à cette plateforme."]],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->username = $user->getUsername();
        $this->isFirstUser = $user->getIsFirst();
       $this->roles = $user->getRoles()[0];

        /*
         * Générons un nouveau token de rafraichissement
         */
        $refreshToken = $user->generateRefreshToken();
        $this->em->persist($refreshToken);
        $this->em->flush();

        //Envoi de mail

        $this->refreshToken = (string)$refreshToken->getId();
        return $this;
    }

    /**
     * Mot de passe oublié
     * @param object $data
     * @throws ExceptionApi
     * @return User|null
     */
    public function forgot(object $data): ?User
    {
        if(isset($data->email) && TypeVariable::is_not_null($data->email) === false){
            throw new ExceptionApi(
                'Veuillez renseigner correctement le nom d\'utilisateur.',
                ['msg' => 'Veuillez renseigner correctement le nom d\'utilisateur.'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        $user = $this->userRepository->findOneBy(['username' => $data->email]);
        if (!$user) {
            throw new ExceptionApi(
                'Il n\'existe aucun utilisateur avec ces identifiant.',
                ['msg' => 'Il n\'existe aucun utilisateur avec ces identifiant.'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        $password = FonctionUtil::password(8);
        $user
            ->setPassword($this->passwordEncoder->encodePassword($user, $password))
            ->setlastLogin(null);
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    /**
     * @param object $data
     * @param Request $request
     * @throws ExceptionApi
     * @return void
     */
    public function logout(object $data, Request $request)
    {
        $token = null;
        try {
            /** @var RefreshToken $refreshToken */
            $refreshToken = $this->refreshTokenRepository->find($data->refreshToken);
            /** @var User $user */
            $user = $this->userRepository->findOneByUuid($data->user);
            if ($refreshToken) {
                $this->em->remove($refreshToken);
            }
            if ($user) {
                $user->setLastLogin(new \DateTime());
                $user->setIsOnline(false);
                $this->em->persist($user);
            }
            $this->em->flush();

            $extractor = new AuthorizationHeaderTokenExtractor('Bearer', 'Authorization');
            $token = $extractor->extract($request);
            $this->encoder->decode($token);
        } catch (\Exception $e) {
            throw new ExceptionApi('Votre session précédente a expirée.',
                ['msg' => 'Votre session précédente a expirée.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        return $token;
    }

    /**
     * @return array
     */
    public function getAccessToken(): array
    {
        try {
            $token = $this->jwtEncoder->encode(['username' => $this->username]);
        } catch (JWTEncodeFailureException $e) {
            $token = null;
        }
        // /** @var Setting $setting */
        // $setting = $this->settingRepository->findOneByAgency(null);
        // $prcFraisOrange = $setting->getPrcFraisOrange();
        // $prcFraisMtn = $setting->getPrcFraisMtn();
        // $prcFraisMoov = $setting->getPrcFraisMoov();
        // $prcFraisWave = $setting->getPrcFraisWave();
        // $prcFraisDebitcard = $setting->getPrcFraisDebitcard();
        $user = $this->userRepository->findOneBy(['username' => $this->username]);
       // $subscribe = count($user->getAgency() ? $user->getAgency()->getSubscriptions() : []) > 0 ? true : false;
      
         $userModel = new UserModel();
        $userModel
            ->setNom($user->getLibelle())
            ->setCivilite($user->getCivilite())
            ->setSexe($user->getSexe())
            ->setPhoto($user->getPhotoSrc())
            ->setAgencyKey($user->getAgency() ? $user->getAgency()->getUuid() : null)
            ->setAgencyName($user->getAgency() ? $user->getAgency()->getNom() : null)
            // ->setAutorisation($user->getAgency() !== null ? $user->getAgency()->getAutorisation()  : null)
            ->setPermissions($user->getPermissions())
            ->setToken($token)
             ->setRole($this->roles)
            ->setTelephone($user->getContact())
            ->setEmail($user->getEmail())
            ->setIsFirstUser($this->isFirstUser)
            ->setUuid($user->getUuid())
            ->setLastLogin($user->getLastLogin());
            // ->setPath($user->getPath());

        $user->setIsOnline(true);
        $this->em->persist($user);
        $this->em->flush();

        $data = $userModel->getData();
        $data['refreshToken'] = $this->refreshToken;
        return $data;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        $token = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyz', 5)), 0, 100);
        return $token;
    }

    /**
     * @param string $token
     * @return User|null
     */
    public function getCurrentUser(string $token): ?User
    {
        try {
            $credential = $this->jwtEncoder->decode($token);
        } catch (JWTDecodeFailureException $e) {
            $credential = null;
        }
        $user = null;
        if (isset($credential['username'])) {
            $username = $credential['username'];
            $user = $this->userRepository->findOneBy(['username' => $username]);
        }
        return $user;
    }

    /**
     * Modifier le mot de passe
     * @param object $data
     * @param User $user
     * @return User
     * @throws ExceptionApi
     */
    public function editPassword(object $data, User $user): User
    {
       $this->checkRequirements($data, $user, 'edit');
       $user->setPassword($this->passwordEncoder->encodePassword($user, $data->new));
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    // /**
    //  * Reinitialiser les accès utilisateur
    //  * @param object $data
    //  * @return User
    //  * @throws ExceptionApi
    //  */
    // public function resetAccess(object $data, ?Agency $agency): User
    // {
    //     $tenant = null;
    //     $customer = null;
    //     $owner = null;
    //     if($data->type === 'LOCATAIRE') {
    //         /** @var Tenant $tenant */
    //         $tenant = $this->tenantRepository->findOneByUuid($data->user);
    //     } elseif ($data->type === 'CLIENT') {
    //         /** @var Customer $customer */
    //         $customer = $this->customerRepository->findOneByUuid($data->user);
    //     } elseif ($data->type === 'PROPRIETAIRE') {
    //         /** @var Owner $owner */
    //         $owner = $this->ownerRepository->findOneByUuid($data->user);
    //     }
    //     /** @var User $user */
    //     $user = $this->userRepository->findOneBy(['tenant' => $tenant, 'customer' => $customer, 'owner' => $owner]);
    //     if(!$user) {
    //         throw new ExceptionApi("L'utilisateur est introuvable.",
    //             ['msg' => "L'utilisateur est introuvable."], Response::HTTP_UNPROCESSABLE_ENTITY
    //         );
    //     }
    //     /** @var User $existe */
    //     $existe = $this->userRepository->findOneByUsername($data->username);
    //     if ($existe && $existe->getId() !== $user->getId()) {
    //         throw new ExceptionApi("Ce nom d'utilisateur est déjà associé à un compte.",
    //             ["msg" => "Ce nom d'utilisateur est déjà associé à un compte."],
    //             Response::HTTP_UNPROCESSABLE_ENTITY
    //         );
    //     }
    //     $user
    //         ->setUsername($data->username)
    //         ->setPassword($this->passwordEncoder->encodePassword($user, $data->password))
    //         ->setIsEnabled(true)
    //     ;
    //     $this->em->persist($user);
    //     $this->em->flush();
    //     $this->securityMailing->userCreate($user, $data->password, $agency);
    //     return $user;
    // }

    // /**
    //  * Reinitialiser un mot de passe
    //  * @param object $data
    //  * @return User
    //  * @throws ExceptionApi
    //  */
    public function resetPassword(object $data): User
    {
        $user = $this->userRepository->findOneByUuid($data->user);
        $this->checkRequirements($data, $user, 'rest');
        $user->setPassword($this->passwordEncoder->encodePassword($user, $data->new));
        $this->em->persist($user);
        $this->em->flush();
        $this->securityMailing->resetPassword($user, $data->new);
        return $user;
    }

    // /**
    //  * @param User $user
    //  * @return void
    //  */
    // public function changeIp(User $user)
    // {
    //     $ip = '';
    //     if ($user->getCurrentAdresse()) {
    //         $user->setCurrentAdresse($ip);
    //         $user->setLastAdresse($ip);
    //         $this->em->persist($user);
    //     } else {
    //         if ($user->getCurrentAdresse() !== $ip) {
    //             $user->setlastAdresse($user->getCurrentAdresse());
    //             $user->setCurrentAdresse($ip);
    //             $this->em->persist($user);
    //         }
    //     }
    //     $this->em->flush();
    //     // Envoi de mail
    //     $this->securityMailing->changeIp($user);
    //     $this->securitySms->changeIp($user);
    // }

    /**
     * @param object $data
     * @param User|null $user
     * @param string $verif
     * @return void
     * @throws ExceptionApi
     */
    public function checkRequirements(object $data, ?User $user, string $verif)
    {
        if ($verif === 'rest') {
        }
        if ($verif === 'edit') {
            $isValid = $this->passwordEncoder->isPasswordValid($user, $data->actuel);
            if (!$isValid) {
                throw new ExceptionApi(
                    "L'ancien mot de passe est incorrect.",
                    ['msg' => "L'ancien mot de passe est incorrect."],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
            if ($data->new !== $data->confirme) {
                throw new ExceptionApi(
                    'Les mots de passes ne correspondent pas.',
                    ['msg' => 'Les mots de passes ne correspondent pas.'],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
        }
        if (!$user) {
            throw new ExceptionApi(
                "Cet utilisateur est introuvable.",
                ['msg' => "Cet utilisateur est introuvable"],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    /**
     * Verifier l'acces a la plateform
     * @param string $plateform
     * @return boolean
     */
    public function isPlateform(string $plateform): bool
    {
        if ($plateform !== 'ADMIN' && $plateform !== 'AGENCY' && $plateform !== 'MOBILE' && $plateform !== 'SITE' && $plateform !== 'PROSPECT') {
            return false;
        }
        return true;
    }
}
