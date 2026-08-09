<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthScope;

interface OAuthScopeRepositoryInterface
{
    /**
     * @return list<OAuthScope>
     */
    public function all(): array;
}
