<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

use InvalidArgumentException;

final readonly class OpenId4VpResponseProtectionContext
{
    public function __construct(
        private string $verifierId,
        private string $nonce,
        private ?string $state = null,
        private ?string $transactionId = null
    ) {
        if (
            trim($this->verifierId) === ''
            || trim($this->nonce) === ''
        ) {
            throw new InvalidArgumentException(
                'OpenID4VP response protection context is invalid.'
            );
        }
    }

    public function verifierId(): string
    {
        return $this->verifierId;
    }

    public function nonce(): string
    {
        return $this->nonce;
    }

    public function state(): ?string
    {
        return $this->state;
    }

    public function transactionId(): ?string
    {
        return $this->transactionId;
    }
}
