<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc;

use InvalidArgumentException;

final readonly class SdJwtVcKeyBindingContext
{
    public function __construct(
        private string $audience,
        private string $nonce,
        private ?string $holderKeyId = null
    ) {
        if (
            trim($this->audience) === ''
            || trim($this->nonce) === ''
        ) {
            throw new InvalidArgumentException(
                'SD-JWT VC key binding context is invalid.'
            );
        }
    }

    public function audience(): string
    {
        return $this->audience;
    }

    public function nonce(): string
    {
        return $this->nonce;
    }

    public function holderKeyId(): ?string
    {
        return $this->holderKeyId;
    }
}
