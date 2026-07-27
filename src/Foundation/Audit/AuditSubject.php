<?php

declare(strict_types=1);

namespace Sif\Foundation\Audit;

use Sif\Foundation\Exceptions\InvalidAuditSubjectException;

final readonly class AuditSubject
{
    public function __construct(
        private string $type,
        private ?string $identifier = null,
    ) {
        if (trim($this->type) === '') {
            throw new InvalidAuditSubjectException('Audit subject type cannot be empty.');
        }

        if ($this->identifier !== null && trim($this->identifier) === '') {
            throw new InvalidAuditSubjectException(
                'Audit subject identifier cannot be empty when provided.',
            );
        }
    }

    public function type(): string
    {
        return $this->type;
    }

    public function identifier(): ?string
    {
        return $this->identifier;
    }

    public function hasIdentifier(): bool
    {
        return $this->identifier !== null;
    }

    public function equals(self $other): bool
    {
        return $this->type === $other->type
            && $this->identifier === $other->identifier;
    }
}
