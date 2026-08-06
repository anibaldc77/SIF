<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Http;

use Sif\Foundation\Contracts\HttpMiddlewareInterface;
use Sif\Foundation\Contracts\RequestHandlerInterface;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\Security\Context\SecurityContext;

final readonly class SecurityContextMiddleware implements HttpMiddlewareInterface
{
    public function __construct(private SecurityContext $context)
    {
    }

    public function process(RequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        return $next->handle($request->withAttribute(
            SecurityRequestAttributes::PRINCIPAL,
            $this->context->principal(),
        ));
    }
}
