<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Password\Protection;

use DateTimeImmutable;
use DateTimeZone;
use Sif\Foundation\Security\Exceptions\InvalidPasswordAttemptProtectionException;

final readonly class PasswordAttemptDecision
{
    private ?DateTimeImmutable $retryAt;

    private function __construct(private bool $allowed, ?DateTimeImmutable $retryAt)
    {
        $this->retryAt = $retryAt?->setTimezone(new DateTimeZone('UTC'));

        if ($allowed === ($this->retryAt !== null)) {
            throw new InvalidPasswordAttemptProtectionException(
                'Allowed decisions cannot define retry time and blocked decisions must define it.'
            );
        }
    }

    public static function allow(): self
    {
        return new self(true, null);
    }

    public static function blockUntil(DateTimeImmutable $retryAt): self
    {
        return new self(false, $retryAt);
    }

    public function isAllowed(): bool
    {
        return $this->allowed;
    }

    public function retryAt(): ?DateTimeImmutable
    {
        return $this->retryAt;
    }
}
