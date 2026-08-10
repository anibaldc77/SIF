<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Metadata\OAuthClientRegistrationManagementAuthorization;
use Sif\Foundation\Security\OAuth\Metadata\OAuthClientRegistrationRecord;
use Sif\Foundation\Security\OAuth\Metadata\OAuthClientRegistrationUpdate;

interface OAuthClientRegistrationManagementServiceInterface
{
    public function get(
        OAuthClientRegistrationManagementAuthorization $authorization
    ): OAuthClientRegistrationRecord;

    public function update(
        OAuthClientRegistrationManagementAuthorization $authorization,
        OAuthClientRegistrationUpdate $update
    ): OAuthClientRegistrationRecord;

    public function delete(
        OAuthClientRegistrationManagementAuthorization $authorization
    ): void;
}
