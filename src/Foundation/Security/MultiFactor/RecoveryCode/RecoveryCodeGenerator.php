<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor\RecoveryCode;

final readonly class RecoveryCodeGenerator
{
    public function __construct(
        private int $count = 8,
        private int $bytesPerCode = 10
    ) {
        if ($count < 1 || $count > 32) {
            throw new \InvalidArgumentException('Recovery code count must be between 1 and 32.');
        }
        if ($bytesPerCode < 8 || $bytesPerCode > 32) {
            throw new \InvalidArgumentException('Recovery code entropy must be between 64 and 256 bits.');
        }
    }

    public function generate(): RecoveryCodeBatch
    {
        $codes = [];

        $bytesPerCode = max(1, $this->bytesPerCode);

        for ($index = 0; $index < $this->count; $index++) {
            $hex = strtoupper(bin2hex(random_bytes($bytesPerCode)));
            $codes[] = new RecoveryCode(implode('-', str_split($hex, 5)));
        }

        return new RecoveryCodeBatch($codes);
    }
}
