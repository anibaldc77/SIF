<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class OpenIdFederationEntityStatement
{
    /**
     * @param list<string> $authorityHints
     * @param array<string, mixed> $metadata
     * @param array<string, mixed> $metadataPolicy
     * @param list<string> $trustMarkIds
     */
    public function __construct(
        private OpenIdFederationEntityStatementKind $kind,
        private string $issuer,
        private string $subject,
        private DateTimeImmutable $issuedAt,
        private DateTimeImmutable $expiresAt,
        private array $authorityHints = [],
        private array $metadata = [],
        private array $metadataPolicy = [],
        private array $trustMarkIds = []
    ) {
        if (
            trim($this->issuer) === ''
            || trim($this->subject) === ''
            || $this->expiresAt <= $this->issuedAt
        ) {
            throw new InvalidArgumentException(
                'OpenID Federation Entity Statement is invalid.'
            );
        }

        if (
            $this->kind === OpenIdFederationEntityStatementKind::EntityConfiguration
            && $this->issuer !== $this->subject
        ) {
            throw new InvalidArgumentException(
                'OpenID Federation Entity Configuration must be self-issued.'
            );
        }
    }

    public function kind(): OpenIdFederationEntityStatementKind
    {
        return $this->kind;
    }

    public function issuer(): string
    {
        return $this->issuer;
    }

    public function subject(): string
    {
        return $this->subject;
    }

    public function issuedAt(): DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    /** @return list<string> */
    public function authorityHints(): array
    {
        return $this->authorityHints;
    }

    /** @return array<string, mixed> */
    public function metadata(): array
    {
        return $this->metadata;
    }

    /** @return array<string, mixed> */
    public function metadataPolicy(): array
    {
        return $this->metadataPolicy;
    }

    /** @return list<string> */
    public function trustMarkIds(): array
    {
        return $this->trustMarkIds;
    }

    public function isSelfIssued(): bool
    {
        return $this->issuer === $this->subject;
    }
}
