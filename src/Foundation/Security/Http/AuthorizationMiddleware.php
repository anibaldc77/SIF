<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Http;

use Sif\Foundation\Contracts\HttpMiddlewareInterface;
use Sif\Foundation\Contracts\RequestHandlerInterface;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\Security\Authorization\AuthorizationContext;
use Sif\Foundation\Security\Authorization\AuthorizationManager;
use Sif\Foundation\Security\Authorization\AuthorizationRequest;
use Sif\Foundation\Security\Context\SecurityContext;
use Sif\Foundation\Security\Controller\AuthorizationRequirement;

final readonly class AuthorizationMiddleware implements HttpMiddlewareInterface
{
    public function __construct(
        private SecurityContext $context,
        private AuthorizationManager $authorization,
        private AuthorizationRequirement $requirement,
        private SecurityHttpResponseFactory $responses = new SecurityHttpResponseFactory(),
    ) {
    }

    public function process(RequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        $principal = $this->context->principal();
        if (!$principal->isAuthenticated()) {
            return $this->responses->unauthorized();
        }

        $decision = $this->authorization->decide(new AuthorizationRequest(
            $principal,
            $this->requirement->action(),
            $this->requirement->resource(),
            new AuthorizationContext(['transport' => 'http']),
        ));

        return $decision->isAllowed()
            ? $next->handle($request)
            : $this->responses->fromDecision($decision);
    }
}
