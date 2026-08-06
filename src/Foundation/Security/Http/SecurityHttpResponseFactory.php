<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Http;

use Sif\Foundation\Http\Value\Response;
use Sif\Foundation\Security\Authorization\AuthorizationDecision;
use Sif\Foundation\Security\Authorization\AuthorizationFailureReason;

final readonly class SecurityHttpResponseFactory
{
    public function unauthorized(string $challenge = 'Bearer'): Response
    {
        return Response::json([
            'type' => 'about:blank',
            'title' => 'Unauthorized',
            'status' => 401,
        ], 401)->withHeader('WWW-Authenticate', $challenge);
    }

    public function forbidden(): Response
    {
        return Response::json([
            'type' => 'about:blank',
            'title' => 'Forbidden',
            'status' => 403,
        ], 403);
    }

    public function fromDecision(AuthorizationDecision $decision): Response
    {
        return $decision->reason() === AuthorizationFailureReason::ANONYMOUS
            ? $this->unauthorized()
            : $this->forbidden();
    }
}
