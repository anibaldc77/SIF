<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthAccessToken;

interface OAuthAccessTokenRepositoryInterface
{
    public function save(OAuthAccessToken $token): void;

    public function find(string $value): ?OAuthAccessToken;

    public function revoke(string $value): void;
}
