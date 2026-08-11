<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Status\Bitstring;

use InvalidArgumentException;
use Sif\Foundation\Security\VerifiableCredentials\Status\CredentialStatusPurpose;

final readonly class BitstringStatusList
{
    public const MINIMUM_BIT_LENGTH = 131072;

    public function __construct(
        private string $listUri,
        private CredentialStatusPurpose $purpose,
        private string $bitstring,
        private int $statusSize = 1
    ) {
        if (trim($this->listUri) === '') {
            throw new InvalidArgumentException('Bitstring status list URI is required.');
        }

        if ($this->statusSize < 1 || $this->statusSize > 8) {
            throw new InvalidArgumentException('Bitstring status size must be between 1 and 8 bits.');
        }

        if ($this->bitLength() < self::MINIMUM_BIT_LENGTH) {
            throw new InvalidArgumentException('Bitstring status list is shorter than the minimum size.');
        }
    }

    public function listUri(): string
    {
        return $this->listUri;
    }

    public function purpose(): CredentialStatusPurpose
    {
        return $this->purpose;
    }

    public function statusSize(): int
    {
        return $this->statusSize;
    }

    public function bitLength(): int
    {
        return strlen($this->bitstring) * 8;
    }

    public function entryCapacity(): int
    {
        return intdiv($this->bitLength(), $this->statusSize);
    }

    public function bitstring(): string
    {
        return $this->bitstring;
    }
}
