<?php

namespace App\Controller\Security;

use App\Entity\Admin\User;
use App\Helpers\JsonHelper;
use App\Security\JwtEncoder;
use App\Helpers\AgencyHelper;
use App\Exception\ExceptionApi;
use App\Entity\Extra\RefreshToken;
use Doctrine\ORM\EntityManagerInterface;
use App\Manager\Security\SecurityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\Extra\RefreshTokenRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;

class SecurityController extends AbstractController
{
    private $em;
    /** @var string|\Stringable|\Symfony\Component\Security\Core\User\UserInterface */
    private $user;
    private $securityManager;
    private $refreshTokenRepository;
    public function __construct(
        EntityManagerInterface $em,
        SecurityManager $securityManager,
        TokenStorageInterface $tokenStorage,
        RefreshTokenRepository $refreshTokenRepository
    ) {
        $this->em = $em;
        $this->securityManager = $securityManager;
        $this->refreshTokenRepository = $refreshTokenRepository;
        if ($tokenStorage->getToken()) {
            $this->user = $tokenStorage->getToken()->getUser();
        }
    }

    /**
     * @Route("/api/login", name="login", methods={"POST","GET"}, 
     * options={"description"="Se connecter"})
     */
    public function login(Request $request)
    {
        try {
            $token = $this->securityManager->checkCredential($request)->getAccessToken();
            $response = (new JsonHelper($token, 'Connexion reussie', 'success', 200, []))->serialize();
        } catch (ExceptionApi $e) {
            $response = (new JsonHelper(null, $e->getMessage(), 'bad_request', $e->getCode(), $e->getErrors()))->serialize();
            return $this->json($response, $e->getCode(), [], ['groups' => ["file", "photo"]]);
        }
        return $this->json($response);
    }


    /**
     * @Route("/test", name="login1", methods={"POST","GET"}, 
     * options={"description"="Se connecter"})
     */
    public function login1(Request $request)
    {
        dd("bonnnnnnn");
        // try {
        //     $token = $this->securityManager->checkCredential($request)->getAccessToken();
        //     $response = (new JsonHelper($token, 'Connexion reussie', 'success', 200, []))->serialize();
        // } catch (ExceptionApi $e) {
        //     $response = (new JsonHelper(null, $e->getMessage(), 'bad_request', $e->getCode(), $e->getErrors()))->serialize();
        //     return $this->json($response, $e->getCode(), [], ['groups' => ["file", "photo"]]);
        // }
        // return $this->json($response);
    }

    /**
     * @Route("/api/logout", name="logout", methods={"POST"}, 
     * options={"description"="Se deconnecter"})
     */
    public function logout(Request $request)
    {
        $content = json_decode($request->getContent());
        try {
            $token = $this->securityManager->logout($content, $request);
            $response = (new JsonHelper($token, 'Déconnexion réussie', 'success', 200, []))->serialize();
        } catch (ExceptionApi $e) {
            $response = (new JsonHelper(null, $e->getMessage(), 'bad_request', $e->getCode(), $e->getErrors()))->serialize();
            return $this->json($response, $e->getCode(), [], ['groups' => ["file", "photo"]]);
        }
        return $this->json($response);
    }

    /**
     * @Route("/api/forgot", name="forgot_password", methods={"POST","GET"}, 
     * options={"description"="Mot de passe oublié"})
     */
    public function forgot(Request $request)
    {
        try {
            $data = \json_decode($request->getContent());
            $user = $this->securityManager->forgot($data);
            $response = (new JsonHelper($user, 'Email envoyer avec success', 'success', 200, []))->serialize();
        } catch (ExceptionApi $e) {
            $response = (new JsonHelper(null, $e->getMessage(), 'bad_request', $e->getCode(), $e->getErrors()))->serialize();
            return $this->json($response, $e->getCode(), [], ['groups' => ["user", "file", "photo"]]);
        }
        return $this->json($response, 200, [], ['groups' => ["user", "file", "photo"]]);
    }

    /**
     * @Route("/api/token/refresh", name="api_refresh")
     * @param Request $request
     * @param JWTEncoderInterface $encoder
     * @return JsonResponse|void
     * @throws \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException
     * @throws \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException
     */
    public function refreshTokenAction(Request $request, JwtEncoder $encoder)
    {
        $content = json_decode($request->getContent(), true);
        if (!$request->headers->has('Authorization')) {
            return $this->json(['status' => 'error', 'message' => 'No Token Found']);
        }
        if (!array_key_exists('refreshToken', $content)) {
            return $this->json(['status' => 'error', 'message' => 'request is empty']);
        }
        /** @var RefreshToken $refreshToken */
        $refreshToken = $this->refreshTokenRepository->find($content['refreshToken']);
        if (!$refreshToken) {
            return $this->json(['status' => 'error', 'message' => 'unknow refresh token']);
        }

        $now = new \DateTime();
        if ($refreshToken->getExpireAt() < $now) {
            $when = clone $refreshToken->getExpireAt();
            $this->em->remove($refreshToken);
            $this->em->flush();
            return $this->json(['status' => 'error', 'message' => 'Refresh token expired at ' . $when->format('d-m-Y h:i:s')]);
        }
        $extractor = new AuthorizationHeaderTokenExtractor(
            'Bearer',
            'Authorization'
        );
        $token = $extractor->extract($request);
        try {
            $data = $encoder->decode($token);
        } catch (JWTDecodeFailureException $exception) {
            if ($exception->getReason() === 'expired_token') {
                $data = $exception->getPayload();
            }
        }

        /** @var User $user */
        $user = $refreshToken->getCreateBy();
        $this->em->remove($refreshToken);
        $newRefreshToken = $user->generateRefreshToken();
        $token = $encoder->encode([
            'username' => $user->getUsername()
        ]);
        $this->em->persist($newRefreshToken);
        $this->em->flush();
        $data = [
            'token' => $token,
            'isFirstUser' => $user->getIsFirst(),
            'role' => $user->getRoles()[0],
            'refreshToken' => (string)$newRefreshToken->getId(),
        ];
        $response = (new JsonHelper($data, 'Connexion reussie', 'success', 200, []))->serialize();
        return $this->json($response);
    }

    /**
     * @Route("/api/auth/edit/password", name="edit_passord", 
     * methods={"POST"}, options={"description"="Changer de mot de passe", "permission"="USER:PASSWORD:EDIT"})
     */
    public function editPassword(Request $request)
    {
        try {
            $user = $this->getUser();
            $data = json_decode($request->getContent());
            $user = $this->securityManager->editPassword($data, $user);
            $response = (new JsonHelper($user, 'Votre mot de passe a été modifié avec succès', 'success', 200, []))->serialize();
        } catch (ExceptionApi $e) {
            $response = (new JsonHelper(null, $e->getMessage(), 'bad_request', $e->getCode(), $e->getErrors()))->serialize();
            return $this->json($response, $e->getCode(), [], ['groups' => ["user", "file", "photo"]]);
        }
        return $this->json($response, 200, [], ['groups' => ["user", "file", "photo"]]);
    }

    // /**
    //  * @Route("/api/auth/rest/access", name="reset_access", 
    //  * methods={"POST"}, options={"description"="Réinitialiser les accès utilisateur", "permission"="USER:ACCESS:RESET"})
    //  */
    // public function resetAccess(Request $request)
    // {
    //     try {
    //         $agency = AgencyHelper::agency($this->getUser());
    //         $data = json_decode($request->getContent());
    //         $user = $this->securityManager->resetAccess($data, $agency);
    //         $response = (new JsonHelper($user, 'Votre mot de passe a été réinitialisé avec succès', 'success', 200, []))->serialize();
    //     } catch (ExceptionApi $e) {
    //         $response = (new JsonHelper(null, $e->getMessage(), 'bad_request', $e->getCode(), $e->getErrors()))->serialize();
    //         return $this->json($response, $e->getCode(), [], ['groups' => ["user", "file", "photo"]]);
    //     }
    //     return $this->json($response, 200, [], ['groups' => ["user", "file", "photo"]]);
    // }

    /**
     * @Route("/api/auth/rest/password", name="reset_passord", 
     * methods={"POST"}, options={"description"="Réinitialiser un mot de passe", "permission"="USER:PASSWORD:RESET"})
     */
    public function resetPassword(Request $request)
    {
        try {
            $data = json_decode($request->getContent());
            $user = $this->securityManager->resetPassword($data);
            $response = (new JsonHelper($user, 'Votre mot de passe a été réinitialisé avec succès', 'success', 200, []))->serialize();
        } catch (ExceptionApi $e) {
            $response = (new JsonHelper(null, $e->getMessage(), 'bad_request', $e->getCode(), $e->getErrors()))->serialize();
            return $this->json($response, $e->getCode(), [], ['groups' => ["user", "file", "photo"]]);
        }
        return $this->json($response, 200, [], ['groups' => ["user", "file", "photo"]]);
    }

    /**
     * @Route("/api/test/sms", name="test_sms", methods={"POST","GET"}, 
     * options={"description"="Tester l'API de SMS"})
     */
    public function test_sms(Request $request)
    {
        $response = null;
        try {
            // $res = $this->securityManager->testSms($request);
            // $response = (new JsonHelper($res, $res ? 'SMS envoyé avec succès !' :  'SMS non envoyé !', 'success', 200, []))->serialize();
        } catch (ExceptionApi $e) {
            $response = (new JsonHelper(null, $e->getMessage(), 'bad_request', $e->getCode(), $e->getErrors()))->serialize();
            return $this->json($response, $e->getCode(), [], ['groups' => ["file", "photo"]]);
        }
        return $this->json($response);
    }
}
