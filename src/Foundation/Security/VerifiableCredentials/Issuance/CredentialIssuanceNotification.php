<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;

use InvalidArgumentException;

final readonly class CredentialIssuanceNotification
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private string $notificationId,
        private string $transactionId,
        private string $event,
        private array $metadata = []
    ) {
        if (
            trim($this->notificationId) === ''
            || trim($this->transactionId) === ''
            || trim($this->event) === ''
        ) {
            throw new InvalidArgumentException(
                'Credential issuance notification is invalid.'
            );
        }
    }

    public function notificationId(): string
    {
        return $this->notificationId;
    }

    public function transactionId(): string
    {
        return $this->transactionId;
    }

    public function event(): string
    {
        return $this->event;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }
}
