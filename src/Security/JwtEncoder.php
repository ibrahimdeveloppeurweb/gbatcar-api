<?php

namespace App\Security;

use Namshi\JOSE\JWS;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\LcobucciJWTEncoder;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;


class JwtEncoder extends LcobucciJWTEncoder
{
    public function __construct(JWSProviderInterface $jwsProvider)
    {
        parent::__construct($jwsProvider);
    }

    public function decodeIgnoreExpired($token)
    {
        try {
            /** @var JWS $jws */
            $jws = JWS::load($token);
        } catch (\InvalidArgumentException $e) {
            return false;
        }
        $publicKey = __DIR__ . '/../../config/jwt/public.pem';
        if (!$jws->verify($publicKey)) {
            return false;
        }

        return $jws->getPayload();
    }
}
