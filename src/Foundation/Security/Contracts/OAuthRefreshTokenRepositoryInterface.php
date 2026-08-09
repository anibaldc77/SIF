<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthRefreshToken;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthRefreshTokenFamilyId;

interface OAuthRefreshTokenRepositoryInterface
{
    public function save(OAuthRefreshToken $token): void;

    public function find(string $value): ?OAuthRefreshToken;

    public function revoke(string $value): void;

    public function revokeFamily(
        OAuthRefreshTokenFamilyId $familyId
    ): void;
}
