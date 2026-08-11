<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Status\Issuer;

use InvalidArgumentException;
use Sif\Foundation\Security\VerifiableCredentials\Status\CredentialStatusPurpose;

final readonly class CredentialStatusAllocation
{
    public function __construct(
        private string $credentialId,
        private string $statusListId,
        private int $index,
        private CredentialStatusPurpose $purpose
    ) {
        if (
            trim($this->credentialId) === ''
            || trim($this->statusListId) === ''
            || $this->index < 0
        ) {
            throw new InvalidArgumentException(
                'Credential status allocation is invalid.'
            );
        }
    }

    public function credentialId(): string
    {
        return $this->credentialId;
    }

    public function statusListId(): string
    {
        return $this->statusListId;
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
