<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use DateTimeImmutable;

interface SamlReplayStoreInterface
{
    public function contains(string $identifier): bool;

    public function remember(
        string $identifier,
        DateTimeImmutable $expiresAt
    ): void;
}
