<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc;

use InvalidArgumentException;

final readonly class SdJwtVcDisclosureReference
{
    public function __construct(
        private string $digest,
        private string $hashAlgorithm = 'sha-256'
    ) {
        if (
            trim($this->digest) === ''
            || trim($this->hashAlgorithm) === ''
        ) {
            throw new InvalidArgumentException(
                'SD-JWT VC disclosure reference is invalid.'
            );
        }
    }

    public function digest(): string
    {
        return $this->digest;
    }

    public function hashAlgorithm(): string
    {
        return $this->hashAlgorithm;
    }
}
