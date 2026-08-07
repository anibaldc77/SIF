<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class FederatedSecurityOperationEvent
{
    /**
     * @param array<string, scalar|null> $context
     */
    public function __construct(
        private string $name,
        private DateTimeImmutable $occurredAt,
        private array $context = []
    ) {
        if (
            $this->name === ''
            || strlen($this->name) > 160
            || preg_match('/^[A-Za-z0-9._:-]+$/', $this->name) !== 1
        ) {
            throw new InvalidArgumentException(
                'Federated security operation event name is invalid.'
            );
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    /** @return array<string, scalar|null> */
    public function context(): array
    {
        return $this->context;
    }
}
