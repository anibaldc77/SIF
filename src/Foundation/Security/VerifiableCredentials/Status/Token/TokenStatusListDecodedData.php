<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Status\Token;

final readonly class TokenStatusListDecodedData
{
    public function __construct(
        private string $bytes,
        private int $bitsPerStatus
    ) {
    }

    public function bytes(): string
    {
        return $this->bytes;
    }

    public function bitsPerStatus(): int
    {
        return $this->bitsPerStatus;
    }

    public function entryCount(): int
    {
        if ($this->bitsPerStatus <= 0) {
            return 0;
        }

        return intdiv(strlen($this->bytes) * 8, $this->bitsPerStatus);
    }
}
