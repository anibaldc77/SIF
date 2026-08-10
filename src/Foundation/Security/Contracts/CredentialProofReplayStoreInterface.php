<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use DateTimeImmutable;

interface CredentialProofReplayStoreInterface
{
    public function hasSeen(
        string $proofFingerprint,
        string $nonce
    ): bool;

    public function remember(
        string $proofFingerprint,
        string $nonce,
        DateTimeImmutable $seenAt
    ): void;
}
