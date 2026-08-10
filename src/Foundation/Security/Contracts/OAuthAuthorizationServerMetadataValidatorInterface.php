<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Metadata\OAuthAuthorizationServerMetadata;

interface OAuthAuthorizationServerMetadataValidatorInterface
{
    public function validate(
        OAuthAuthorizationServerMetadata $metadata
    ): void;
}
