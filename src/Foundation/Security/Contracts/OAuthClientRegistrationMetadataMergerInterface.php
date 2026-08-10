<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Metadata\OAuthClientRegistrationMetadata;
use Sif\Foundation\Security\OAuth\Metadata\OAuthSoftwareStatement;

interface OAuthClientRegistrationMetadataMergerInterface
{
    public function merge(
        OAuthClientRegistrationMetadata $requestedMetadata,
        ?OAuthSoftwareStatement $softwareStatement
    ): OAuthClientRegistrationMetadata;
}
