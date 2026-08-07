<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Diagnostics;

use Sif\Foundation\Security\Authorization\Attribute\AuthorizationAttributeBag;
use Sif\Foundation\Security\Authorization\Cache\CachedPrincipalAuthorizationGrantResolver;
use Sif\Foundation\Security\Authorization\Policy\AdvancedAuthorizationService;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;

final readonly class AdvancedAuthorizationDiagnosticService
{
    public function __construct(
        private AdvancedAuthorizationService $authorization,
        private CachedPrincipalAuthorizationGrantResolver $grants
    ) {
    }

    public function evaluate(
        AuthenticatedPrincipal $principal,
        AuthorizationAttributeBag $resource = new AuthorizationAttributeBag(),
        AuthorizationAttributeBag $environment = new AuthorizationAttributeBag()
    ): AuthorizationDiagnosticSnapshot {
        $resolved = $this->grants->resolve($principal);
        $decision = $this->authorization->decide(
            $principal,
            $resource,
            $environment
        );

        $material = implode('|', [
            $principal->identity()->id()->value(),
            implode(',', $resolved->roles()->values()),
            implode(',', $resolved->permissions()->values()),
            $decision->reason()->value,
            $decision->isAllowed() ? '1' : '0',
        ]);

        return new AuthorizationDiagnosticSnapshot(
            $decision,
            hash('sha256', $principal->identity()->id()->value()),
            $resolved->roles()->count(),
            $resolved->permissions()->count(),
            hash('sha256', $material)
        );
    }
}
