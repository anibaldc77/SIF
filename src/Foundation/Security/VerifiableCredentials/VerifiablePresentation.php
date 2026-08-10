<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials;

use InvalidArgumentException;

final readonly class VerifiablePresentation
{
    /**
     * @param list<VerifiableCredential> $credentials
     */
    public function __construct(
        private string $holder,
        private array $credentials,
        private ?string $nonce = null,
        private ?string $audience = null
    ) {
        if (trim($this->holder) === '' || $this->credentials === []) {
            throw new InvalidArgumentException(
                'Verifiable presentation is invalid.'
            );
        }
    }

    public function holder(): string
    {
        return $this->holder;
    }

    /**
     * @return list<VerifiableCredential>
     */
    public function credentials(): array
    {
        return $this->credentials;
    }

    public function nonce(): ?string
    {
        return $this->nonce;
    }

    public function audience(): ?string
    {
        return $this->audience;
    }
}
