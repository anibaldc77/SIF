<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthDeviceAuthorization;

interface OAuthDeviceAuthorizationApproverInterface
{
    public function approve(
        OAuthDeviceAuthorization $authorization,
        string $subject
    ): OAuthDeviceAuthorization;

    public function deny(
        OAuthDeviceAuthorization $authorization
    ): OAuthDeviceAuthorization;
}
