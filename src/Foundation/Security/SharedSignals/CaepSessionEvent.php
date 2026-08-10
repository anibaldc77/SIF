<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\SharedSignals;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class CaepSessionEvent
{
    /**
     * @param array<string, mixed> $details
     */
    public function __construct(
        private CaepEventType $type,
        private SecurityEventSubject $subject,
        private DateTimeImmutable $occurredAt,
        private array $details = []
    ) {
    }

    public function type(): CaepEventType
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
    public function details(): array
    {
        return $this->details;
    }
}
