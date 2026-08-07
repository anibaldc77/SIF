<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\SamlReplayStoreInterface;
use Sif\Foundation\Security\Exceptions\SamlReplayDetectedException;

final readonly class SamlReplayGuard
{
    public function __construct(
        private SamlReplayStoreInterface $store
    ) {
    }

    public function assertFresh(
        string $identifier,
        DateTimeImmutable $expiresAt
    ): void {
        if ($this->store->contains($identifier)) {
            throw new SamlReplayDetectedException(
                'SAML replay detected for identifier: ' . $identifier
            );
        }

        $this->store->remember(
            $identifier,
            $expiresAt
        );
    }
}
