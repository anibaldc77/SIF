<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc;

use InvalidArgumentException;

final readonly class SdJwtVcDisclosure
{
    public function __construct(
        private string $salt,
        private string $claimName,
        private mixed $claimValue
    ) {
        if (
            trim($this->salt) === ''
            || trim($this->claimName) === ''
        ) {
            throw new InvalidArgumentException(
                'SD-JWT VC disclosure is invalid.'
            );
        }
    }

    public function salt(): string
    {
        return $this->salt;
    }

    public function claimName(): string
    {
        return $this->claimName;
    }

    public function claimValue(): mixed
    {
        return $this->claimValue;
    }
}
