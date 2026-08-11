<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

use InvalidArgumentException;

final readonly class WebAuthnRegistrationContext
{
    public function __construct(
        private string $relyingPartyId,
        private string $origin,
        private string $userId,
        private string $userName,
        private string $challenge
    ) {
        if (
            trim($this->relyingPartyId) === ''
            || trim($this->origin) === ''
            || trim($this->userId) === ''
            || trim($this->userName) === ''
            || trim($this->challenge) === ''
        ) {
            throw new InvalidArgumentException(
                'WebAuthn registration context is invalid.'
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

    public function userId(): string
    {
        return $this->userId;
    }

    public function userName(): string
    {
        return $this->userName;
    }

    public function challenge(): string
    {
        return $this->challenge;
    }
}
