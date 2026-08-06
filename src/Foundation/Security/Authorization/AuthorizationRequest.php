<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization;

use Sif\Foundation\Security\Contracts\PrincipalInterface;

final readonly class AuthorizationRequest
{
    public function __construct(
        private PrincipalInterface $principal,
        private AuthorizationAction $action,
        private AuthorizationResource $resource,
        private AuthorizationContext $context = new AuthorizationContext()
    ) {
    }

    public function principal(): PrincipalInterface { return $this->principal; }
    public function action(): AuthorizationAction { return $this->action; }
    public function resource(): AuthorizationResource { return $this->resource; }
    public function context(): AuthorizationContext { return $this->context; }
}
