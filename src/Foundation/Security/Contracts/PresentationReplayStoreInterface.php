<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use DateTimeImmutable;

interface PresentationReplayStoreInterface
{
    public function hasSeen(
        string $requestId,
        string $nonce
    ): bool;

    public function remember(
        string $requestId,
        string $nonce,
        DateTimeImmutable $seenAt
    ): void;
}
