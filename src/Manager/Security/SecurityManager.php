<?php

namespace App\Manager\Security;

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
    private $refreshToken;
    private $userRepository;
    private $passwordEncoder;
    private $refreshTokenRepository;
    public function __construct(
        JwtEncoder $encoder,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        JWTEncoderInterface $jwtEncoder,
        TokenStorageInterface $tokenStorage,
        UserPasswordEncoderInterface $passwordEncoder,
        RefreshTokenRepository $refreshTokenRepository
        )
    {
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

        }
        catch (NonUniqueResultException $e) {
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
            ['msg' => ["Le compte de cet utilisateur est bloqué. Contactez l'administrateur."]],
                Response::HTTP_UNPROCESSABLE_ENTITY
                );
        }
        if (!$user->isEnabled()) {
            throw new ExceptionApi(
                'Accès refusé',
            ['msg' => ["Le compte de cet utilisateur n'est pas encore validé. Contactez l'administrateur."]],
                Response::HTTP_UNPROCESSABLE_ENTITY
                );
        }

        // Vérifier les autorisations d'accès plateforme ADMIN (backoffice web)
        // Accès autorisé : ROLE_ADMIN et ROLE_MANAGER
        // Accès refusé : ROLE_CLIENT (application mobile uniquement)
        if (isset($body['type']) && $body['type'] === Constants::PLATFORMS['ADMIN']) {
            $allowedRoles = [Constants::USER_ROLES['ADMIN'], Constants::USER_ROLES['MANAGER']];
            if (!in_array($user->getRoles()[0], $allowedRoles, true)) {
                throw new ExceptionApi(
                    'Accès refusé',
                ['msg' => ["Cet utilisateur n'a pas accès à la plateforme d'administration GbatCar."]],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                    );
            }
        }

        // Vérifier les autorisations d'accès MOBILE (application cliente)
        // Accès autorisé : ROLE_CLIENT uniquement
        if (isset($body['type']) && $body['type'] === Constants::PLATFORMS['MOBILE'] && Constants::USER_ROLES['CLIENT'] !== $user->getRoles()[0]) {
            throw new ExceptionApi(
                'Accès refusé',
            ['msg' => ["Cet utilisateur n'a pas accès à l'application mobile GbatCar."]],
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
        if (isset($data->email) && TypeVariable::is_not_null($data->email) === false) {
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
        }
        catch (\Exception $e) {
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
        }
        catch (JWTEncodeFailureException $e) {
            $token = null;
        }
        $user = $this->userRepository->findOneBy(['username' => $this->username]);

        $userModel = new UserModel();
        $userModel
            ->setFirstname($user->getPrenom())
            ->setName($user->getNom())
            ->setIsFirst($user->getIsFirst())
            ->setPhoto($user->getPhotoSrc())
            ->setContact($user->getContact())
            ->setEmail($user->getEmail())
            ->setCountryId($user->getUuid())
            ->setUsername($user->getUsername())
            ->setPermissions($user->getPermissions())
            ->setToken($token)
            ->setRole($this->roles)
            ->setTelephone($user->getContact())
            ->setEmail($user->getEmail())
            ->setIsFirstUser($this->isFirstUser)
            ->setUuid($user->getUuid())
            ->setLastLogin($user->getLastLogin());

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
        }
        catch (JWTDecodeFailureException $e) {
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

    /**
     * Réinitialiser un mot de passe (par un administrateur)
     * @param object $data
     * @return User
     * @throws ExceptionApi
     */
    public function resetPassword(object $data): User
    {
        $user = $this->userRepository->findOneByUuid($data->user);
        $this->checkRequirements($data, $user, 'rest');
        $user->setPassword($this->passwordEncoder->encodePassword($user, $data->new));
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    /**
     * Vérifier l'accès à une plateforme GbatCar
     * @param array $data
     * @param User $user
     * @return bool
     */
    public function checkPlatform(array $data, User $user)
    {
        $platform = isset($data['platform']) ? $data['platform'] : null;
        if ($platform === Constants::PLATFORMS['ADMIN']) {
            return $user->getRoles()[0] === Constants::USER_ROLES['ADMIN'];
        }
        if ($platform === Constants::PLATFORMS['MOBILE']) {
            return $user->getRoles()[0] === Constants::USER_ROLES['CLIENT'];
        }
        return false;
    }

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
     * Vérifier si une plateforme GbatCar est valide
     * @param string $plateform
     * @return bool
     */
    public function isPlateform(string $plateform): bool
    {
        return in_array($plateform, array_values(Constants::PLATFORMS), true);
    }
}