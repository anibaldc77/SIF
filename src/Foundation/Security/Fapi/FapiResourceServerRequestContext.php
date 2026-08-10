<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Fapi;

use InvalidArgumentException;

final readonly class FapiResourceServerRequestContext
{
    public function __construct(
        private string $resource,
        private string $clientId,
        private string $subject,
        private bool $tokenActive,
        private bool $senderConstraintValidated,
        private bool $audienceValidated
    ) {
        if (
            trim($this->resource) === ''
            || trim($this->clientId) === ''
            || trim($this->subject) === ''
        ) {
            throw new InvalidArgumentException(
                'FAPI resource server request context is invalid.'
            );
        }
    }

    public function resource(): string
    {
        return $this->resource;
    }

    public function clientId(): string
    {
        return $this->clientId;
    }

    public function subject(): string
    {
        return $this->subject;
    }

    public function tokenActive(): bool
    {
        return $this->tokenActive;
    }

    public function senderConstraintValidated(): bool
    {
        return $this->senderConstraintValidated;
    }

    public function audienceValidated(): bool
    {
        return $this->audienceValidated;
    }
}
