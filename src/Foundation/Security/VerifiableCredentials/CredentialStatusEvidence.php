<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class CredentialStatusEvidence
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private CredentialStatus $status,
        private DateTimeImmutable $checkedAt,
        private string $source,
        private array $metadata = []
    ) {
        if (trim($this->source) === '') {
            throw new InvalidArgumentException(
                'Credential status evidence source is invalid.'
            );
        }
    }

    public function status(): CredentialStatus
    {
        return $this->status;
    }

    public function checkedAt(): DateTimeImmutable
    {
        return $this->checkedAt;
    }

    public function source(): string
    {
        return $this->source;
    }

    /** @return array<string, mixed> */
    public function metadata(): array
    {
        return $this->metadata;
    }
}
