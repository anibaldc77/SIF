<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Integration;

use Sif\Foundation\Security\Authorization\AuthorizationDecision;

final readonly class ControllerAuthorizationBridge
{
    public function __construct(
        private AdvancedAuthorizationGuard $guard
    ) {
    }

    public function authorize(
        AdvancedAuthorizationRequest $request
    ): AuthorizationDecision {
        return $this->guard->decide($request);
    }
}
