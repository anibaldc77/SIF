<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Metadata\OAuthAuthorizationServerMetadata;

interface OAuthAuthorizationServerMetadataProviderInterface
{
    public function metadata(): OAuthAuthorizationServerMetadata;
}
