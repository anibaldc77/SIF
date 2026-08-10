<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

interface OAuthRegistrationAccessTokenRotatorInterface
{
    public function rotate(
        string $clientId,
        string $currentReference
    ): string;
}
