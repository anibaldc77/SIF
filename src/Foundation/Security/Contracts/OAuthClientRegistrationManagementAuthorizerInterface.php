<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Metadata\OAuthClientRegistrationManagementAuthorization;
use Sif\Foundation\Security\OAuth\Metadata\OAuthClientRegistrationRecord;

interface OAuthClientRegistrationManagementAuthorizerInterface
{
    public function authorize(
        OAuthClientRegistrationManagementAuthorization $authorization,
        OAuthClientRegistrationRecord $record
    ): void;
}
