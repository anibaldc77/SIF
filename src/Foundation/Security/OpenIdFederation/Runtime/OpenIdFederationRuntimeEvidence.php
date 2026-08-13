<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation\Runtime;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class OpenIdFederationRuntimeEvidence
{
    public function __construct(
        private string $entityId,
        private DateTimeImmutable $resolvedAt,
        private DateTimeImmutable $freshUntil,
        private DateTimeImmutable $staleUntil,
        private string $sourceVersion
    ) {
        if (
            trim($this->entityId) === ''
            || trim($this->sourceVersion) === ''
            || $this->freshUntil < $this->resolvedAt
            || $this->staleUntil < $this->freshUntil
        ) {
            throw new InvalidArgumentException(
                'OpenID Federation runtime evidence is invalid.'
            );
        }
    }

    public function entityId(): string
    {
        return $this->entityId;
    }

    public function resolvedAt(): DateTimeImmutable
    {
        return $this->resolvedAt;
    }

    public function freshUntil(): DateTimeImmutable
    {
        return $this->freshUntil;
    }

    public function staleUntil(): DateTimeImmutable
    {
        return $this->staleUntil;
    }

    public function sourceVersion(): string
    {
        return $this->sourceVersion;
    }

    public function freshnessAt(DateTimeImmutable $at): OpenIdFederationRuntimeFreshnessStatus
    {
        if ($at <= $this->freshUntil) {
            return OpenIdFederationRuntimeFreshnessStatus::Fresh;
        }

        if ($at <= $this->staleUntil) {
            return OpenIdFederationRuntimeFreshnessStatus::StaleUsable;
        }

        return OpenIdFederationRuntimeFreshnessStatus::Expired;
    }
}
