<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials;

final readonly class IdentityAssuranceEvidence
{
    /**
     * @param array<string, mixed> $details
     */
    public function __construct(
        private string $type,
        private string $method,
        private array $details = []
    ) {
    }

    public function type(): string
    {
        return $this->type;
    }

    public function method(): string
    {
        return $this->method;
    }

    /**
     * @return array<string, mixed>
     */
    public function details(): array
    {
        return $this->details;
    }
}
