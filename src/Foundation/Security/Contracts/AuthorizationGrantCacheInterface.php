<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Authorization\Permission\ResolvedAuthorizationGrants;
use Sif\Foundation\Security\Identity\IdentityId;

interface AuthorizationGrantCacheInterface
{
    public function get(IdentityId $identityId): ?ResolvedAuthorizationGrants;

    public function put(
        IdentityId $identityId,
        ResolvedAuthorizationGrants $grants
    ): void;

    public function invalidate(IdentityId $identityId): void;

    public function clear(): void;
}
