<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Status;

use InvalidArgumentException;

final readonly class CredentialStatusReference
{
    public function __construct(
        private string $credentialId,
        private string $statusListUri,
        private int $index,
        private CredentialStatusPurpose $purpose
    ) {
        if (
            trim($this->credentialId) === ''
            || trim($this->statusListUri) === ''
            || $this->index < 0
        ) {
            throw new InvalidArgumentException(
                'Credential status reference is invalid.'
            );
        }
    }

    public function credentialId(): string
    {
        return $this->credentialId;
    }

    public function statusListUri(): string
    {
        return $this->statusListUri;
    }

    public function index(): int
    {
        return $this->index;
    }

    public function purpose(): CredentialStatusPurpose
    {
        return $this->purpose;
    }
}
