<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth2\AccessToken;

interface BearerTokenExtractorInterface
{
    public function extract(string $authorizationHeader): ?AccessToken;
}
