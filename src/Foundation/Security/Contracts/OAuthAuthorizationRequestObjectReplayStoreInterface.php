<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use DateTimeImmutable;

interface OAuthAuthorizationRequestObjectReplayStoreInterface
{
    public function hasBeenUsed(string $tokenId): bool;

    public function markUsed(
        string $tokenId,
        DateTimeImmutable $expiresAt
    ): void;
}
