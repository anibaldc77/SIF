<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthSigningKey;

interface OAuthSigningKeyProviderInterface
{
    public function current(): OAuthSigningKey;

    public function find(string $keyId): ?OAuthSigningKey;
}
