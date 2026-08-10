<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use DateTimeImmutable;

interface SecurityEventTokenReplayStoreInterface
{
    public function hasSeen(
        string $issuer,
        string $tokenId
    ): bool;

    public function remember(
        string $issuer,
        string $tokenId,
        DateTimeImmutable $seenAt
    ): void;
}
