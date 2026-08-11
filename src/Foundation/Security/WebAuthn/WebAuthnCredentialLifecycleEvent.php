<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class WebAuthnCredentialLifecycleEvent
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private string $credentialId,
        private WebAuthnCredentialLifecycleStatus $status,
        private DateTimeImmutable $occurredAt,
        private string $reason,
        private array $metadata = []
    ) {
        if (
            trim($this->credentialId) === ''
            || trim($this->reason) === ''
        ) {
            throw new InvalidArgumentException(
                'WebAuthn credential lifecycle event is invalid.'
            );
        }
    }

    public function credentialId(): string
    {
        return $this->credentialId;
    }

    public function status(): WebAuthnCredentialLifecycleStatus
    {
        return $this->status;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function reason(): string
    {
        return $this->reason;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }
}
