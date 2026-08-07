<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Integration;

use Sif\Foundation\Security\Authorization\AuthorizationDecision;
use Sif\Foundation\Security\Authorization\Policy\AdvancedAuthorizationService;

final readonly class AdvancedAuthorizationGuard
{
    public function __construct(
        private AdvancedAuthorizationService $authorization
    ) {
    }

    public function decide(
        AdvancedAuthorizationRequest $request
    ): AuthorizationDecision {
        return $this->authorization->decide(
            $request->principal(),
            $request->resource(),
            $request->environment()
        );
    }

    public function isAllowed(
        AdvancedAuthorizationRequest $request
    ): bool {
        return $this->decide($request)->isAllowed();
    }
}
