<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2;

final readonly class BearerAuthenticationFailure
{
    public function __construct(
        private int $statusCode,
        private BearerError $error,
        private BearerChallenge $challenge
    ) {
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function error(): BearerError
    {
        return $this->error;
    }

    public function challenge(): BearerChallenge
    {
        return $this->challenge;
    }
}
