<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Metadata\OAuthClientMetadataValidationResult;
use Sif\Foundation\Security\OAuth\Metadata\OAuthClientRegistrationMetadata;
use Sif\Foundation\Security\OAuth\Metadata\OAuthSoftwareStatement;

interface OAuthClientRegistrationMetadataValidatorInterface
{
    public function validate(
        OAuthClientRegistrationMetadata $metadata
    ): void;

    public function validateWithContext(
        OAuthClientRegistrationMetadata $metadata,
        ?OAuthSoftwareStatement $softwareStatement
    ): OAuthClientMetadataValidationResult;
}
