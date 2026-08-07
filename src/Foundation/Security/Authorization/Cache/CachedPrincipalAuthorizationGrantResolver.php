<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Cache;

use Sif\Foundation\Security\Authorization\Permission\PrincipalAuthorizationGrantResolver;
use Sif\Foundation\Security\Authorization\Permission\ResolvedAuthorizationGrants;
use Sif\Foundation\Security\Contracts\AuthorizationGrantCacheInterface;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;

final readonly class CachedPrincipalAuthorizationGrantResolver
{
    public function __construct(
        private PrincipalAuthorizationGrantResolver $inner,
        private AuthorizationGrantCacheInterface $cache
    ) {
    }

    public function resolve(
        AuthenticatedPrincipal $principal
    ): ResolvedAuthorizationGrants {
        $identityId = $principal->identity()->id();

        $cached = $this->cache->get($identityId);
        if ($cached !== null) {
            return $cached;
        }

        $resolved = $this->inner->resolve($principal);
        $this->cache->put($identityId, $resolved);

        return $resolved;
    }

    public function invalidate(AuthenticatedPrincipal $principal): void
    {
        $this->cache->invalidate($principal->identity()->id());
    }
}
