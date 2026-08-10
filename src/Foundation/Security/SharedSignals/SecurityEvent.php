<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\SharedSignals;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class SecurityEvent
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        private string $type,
        private SecurityEventSubject $subject,
        private DateTimeImmutable $occurredAt,
        private array $payload = []
    ) {
        if (trim($this->type) === '') {
            throw new InvalidArgumentException(
                'Security event type is invalid.'
            );
        }
    }

    public function type(): string
    {
        return $this->type;
    }

    public function subject(): SecurityEventSubject
    {
        return $this->subject;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }
}
