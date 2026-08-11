<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Status\Token;

use InvalidArgumentException;

final readonly class TokenStatusList
{
    public function __construct(
        private string $encodedList,
        private int $bitsPerStatus,
        private ?int $timeToLive = null,
        private ?string $aggregationUri = null
    ) {
        if (
            trim($this->encodedList) === ''
            || $this->bitsPerStatus < 1
            || $this->bitsPerStatus > 8
            || ($this->timeToLive !== null && $this->timeToLive <= 0)
        ) {
            throw new InvalidArgumentException(
                'Token Status List is invalid.'
            );
        }
    }

    public function encodedList(): string
    {
        return $this->encodedList;
    }

    public function bitsPerStatus(): int
    {
        return $this->bitsPerStatus;
    }

    public function timeToLive(): ?int
    {
        return $this->timeToLive;
    }

    public function aggregationUri(): ?string
    {
        return $this->aggregationUri;
    }
}
