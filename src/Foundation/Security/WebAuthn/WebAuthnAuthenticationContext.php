<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

use InvalidArgumentException;

final readonly class WebAuthnAuthenticationContext
{
    public function __construct(
        private string $relyingPartyId,
        private string $origin,
        private string $challenge,
        private ?string $userHandle = null
    ) {
        if (
            trim($this->relyingPartyId) === ''
            || trim($this->origin) === ''
            || trim($this->challenge) === ''
        ) {
            throw new InvalidArgumentException(
                'WebAuthn authentication context is invalid.'
            );
        }
    }

    public function relyingPartyId(): string
    {
        return $this->relyingPartyId;
    }

    public function origin(): string
    {
        return $this->origin;
    }

    public function challenge(): string
    {
        return $this->challenge;
    }

    public function userHandle(): ?string
    {
        return $this->userHandle;
    }
}
