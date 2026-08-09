<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthDeviceAuthorization;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthUserCode;

interface OAuthDeviceAuthorizationRepositoryInterface
{
    public function save(
        OAuthDeviceAuthorization $authorization
    ): void;

    public function findByDeviceCode(
        string $deviceCode
    ): ?OAuthDeviceAuthorization;

    public function findByUserCode(
        OAuthUserCode $userCode
    ): ?OAuthDeviceAuthorization;
}
