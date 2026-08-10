<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

interface OAuthProtectedResourceMetadataUriResolverInterface
{
    public function resolve(
        string $resource
    ): string;
}
