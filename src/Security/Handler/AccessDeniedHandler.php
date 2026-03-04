<?php

namespace App\Security\Handler;

use App\Helpers\JsonHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        return new JsonResponse((new JsonHelper(null,
        'Accès refusé',
        'access_denied', '403'))->serialize(),
        Response::HTTP_FORBIDDEN);
    }
}
