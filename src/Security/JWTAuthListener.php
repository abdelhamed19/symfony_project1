<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class JWTAuthListener
{
    private $jwtManager;

    public function __construct(JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        $protectedRoutes = [
            '/api/protected',
            '/api/another-protected-route',
        ];

        if (!in_array($request->getPathInfo(), $protectedRoutes)) {
            return;
        }

        $extractor = new AuthorizationHeaderTokenExtractor('Bearer', 'Authorization');
        $token = $extractor->extract($request);

        if (!$token) {
            $event->setResponse(new JsonResponse(['error' => 'Missing or invalid JWT token'], 401));
            return;
        }

        try {
            $payload = $this->jwtManager->parse($token) ?? null;
            if (!$payload) {
                return new JsonResponse(['error' => 'Invalid JWT token'], 401);
            }

        } catch (AuthenticationException $e) {
            $event->setResponse(new JsonResponse(['error' => 'Unauthorized: ' . $e->getMessage()], 401));
        }
    }
}
