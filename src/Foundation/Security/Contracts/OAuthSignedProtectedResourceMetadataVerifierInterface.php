<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Metadata\OAuthProtectedResourceMetadata;

interface OAuthSignedProtectedResourceMetadataVerifierInterface
{
    public function verify(
        string $signedMetadata
    ): OAuthProtectedResourceMetadata;
}
