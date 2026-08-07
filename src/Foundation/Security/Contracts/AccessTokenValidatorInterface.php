<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use DateTimeImmutable;
use Sif\Foundation\Security\OAuth2\AccessToken;
use Sif\Foundation\Security\OAuth2\ValidatedAccessToken;

interface AccessTokenValidatorInterface
{
    public function validate(
        AccessToken $token,
        DateTimeImmutable $now
    ): ?ValidatedAccessToken;
}
