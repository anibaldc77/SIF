<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use DateTimeImmutable;

interface WebAuthnChallengeStoreInterface
{
    public function hasSeen(string $challenge): bool;

    public function remember(
        string $challenge,
        DateTimeImmutable $expiresAt
    ): void;

    public function consume(string $challenge): void;
}
