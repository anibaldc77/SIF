<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials;

use InvalidArgumentException;

final readonly class HolderBindingContext
{
    public function __construct(
        private string $expectedHolder,
        private string $audience,
        private string $nonce
    ) {
        if (
            trim($this->expectedHolder) === ''
            || trim($this->audience) === ''
            || trim($this->nonce) === ''
        ) {
            throw new InvalidArgumentException(
                'Holder binding context is invalid.'
            );
        }
    }

    public function expectedHolder(): string
    {
        return $this->expectedHolder;
    }

    public function audience(): string
    {
        return $this->audience;
    }

    public function nonce(): string
    {
        return $this->nonce;
    }
}
