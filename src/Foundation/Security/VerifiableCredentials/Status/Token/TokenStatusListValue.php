<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Status\Token;

use InvalidArgumentException;

final readonly class TokenStatusListValue
{
    public function __construct(
        private int $value,
        private int $bitsPerStatus
    ) {
        if (
            $this->bitsPerStatus < 1
            || $this->bitsPerStatus > 8
            || $this->value < 0
            || $this->value >= (1 << $this->bitsPerStatus)
        ) {
            throw new InvalidArgumentException(
                'Token Status List value is invalid.'
            );
        }
    }

    public function value(): int
    {
        return $this->value;
    }

    public function bitsPerStatus(): int
    {
        return $this->bitsPerStatus;
    }
}
