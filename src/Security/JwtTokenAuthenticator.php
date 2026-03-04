<?php

namespace App\Security;

use App\Helpers\JsonHelper;
use App\Repository\Admin\UserRepository as AdminUserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;

class JwtTokenAuthenticator extends AbstractGuardAuthenticator
{
    private $jwtEncoder;
    private $userRepository;
    public function __construct(
        AdminUserRepository $userRepository,
        JWTEncoderInterface $jwtEncoder
    )
    {
        $this->jwtEncoder = $jwtEncoder;
        $this->userRepository = $userRepository;
    }

    public function supports(Request $request)
    {
        return true;
    }

    public function getCredentials(Request $request)
    {
        $extractor = new AuthorizationHeaderTokenExtractor(
            'Bearer',
            'Authorization');
        error_log('Bearer.' . $request->getPathInfo());
        error_log(json_encode($request->headers->all()));
        return ['token' => $extractor->extract($request)];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $accessToken = $credentials['token'];
        if (null === $accessToken) {
            return null;
        }
        try {
            $credentials = $this->jwtEncoder->decode($accessToken);
        } catch (JWTDecodeFailureException $e) {
            return null;
        }
        if (empty($credentials) || !isset($credentials['username'])) {
            return null;
        }

        $user = $this->userRepository->findOneBy(['username' => $credentials['username']]);
        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse((new JsonHelper(null,
            strtr($exception->getMessageKey(), $exception->getMessageData()),
            'not_authorized', '403'))->serialize(),
            Response::HTTP_FORBIDDEN);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    public function supportsRememberMe()
    {
        return false;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new JsonResponse((new JsonHelper(null,
            'Authentication Required',
            'not_authorized', '403'))->serialize(),
            Response::HTTP_FORBIDDEN);
    }
}
