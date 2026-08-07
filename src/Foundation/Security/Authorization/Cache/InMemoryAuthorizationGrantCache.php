<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Cache;

use Sif\Foundation\Security\Authorization\Permission\ResolvedAuthorizationGrants;
use Sif\Foundation\Security\Contracts\AuthorizationGrantCacheInterface;
use Sif\Foundation\Security\Identity\IdentityId;

final class InMemoryAuthorizationGrantCache implements AuthorizationGrantCacheInterface
{
    /** @var array<string, ResolvedAuthorizationGrants> */
    private array $entries = [];

    public function get(IdentityId $identityId): ?ResolvedAuthorizationGrants
    {
        return $this->entries[$identityId->value()] ?? null;
    }

    public function put(
        IdentityId $identityId,
        ResolvedAuthorizationGrants $grants
    ): void {
        $this->entries[$identityId->value()] = $grants;
    }

    public function invalidate(IdentityId $identityId): void
    {
        unset($this->entries[$identityId->value()]);
    }

    public function clear(): void
    {
        $this->entries = [];
    }
}
