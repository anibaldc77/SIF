<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\SharedSignals;

use InvalidArgumentException;

final readonly class ContinuousAccessContext
{
    public function __construct(
        private SecurityEventSubject $subject,
        private ?string $sessionId = null,
        private ?string $tokenId = null,
        private ?string $clientId = null
    ) {
        if (
            $this->sessionId === null
            && $this->tokenId === null
            && $this->clientId === null
        ) {
            throw new InvalidArgumentException(
                'Continuous access context requires at least one target.'
            );
        }
    }

    public function subject(): SecurityEventSubject
    {
        return $this->subject;
    }

    public function sessionId(): ?string
    {
        return $this->sessionId;
    }

    public function tokenId(): ?string
    {
        return $this->tokenId;
    }

    public function clientId(): ?string
    {
        return $this->clientId;
    }
}
