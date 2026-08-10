<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Metadata\OAuthAuthorizationServerMetadata;
use Sif\Foundation\Security\OAuth\Metadata\OAuthIssuerIdentifier;

interface OAuthAuthorizationServerIssuerValidatorInterface
{
    public function validate(
        OAuthIssuerIdentifier $expectedIssuer,
        OAuthAuthorizationServerMetadata $metadata
    ): void;
}
