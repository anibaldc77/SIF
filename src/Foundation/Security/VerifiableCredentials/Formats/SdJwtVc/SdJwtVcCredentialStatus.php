<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc;

use InvalidArgumentException;

final readonly class SdJwtVcCredentialStatus
{
    public function __construct(
        private string $statusType,
        private string $statusReference
    ) {
        if (
            trim($this->statusType) === ''
            || trim($this->statusReference) === ''
        ) {
            throw new InvalidArgumentException(
                'SD-JWT VC credential status is invalid.'
            );
        }
    }

    public function statusType(): string
    {
        return $this->statusType;
    }

    public function statusReference(): string
    {
        return $this->statusReference;
    }
}
