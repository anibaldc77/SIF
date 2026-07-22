<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration;

use InvalidArgumentException;

final readonly class ConfigurationDiagnostic
{
    /** @param array<string, scalar|null> $context */
    public function __construct(
        public string $code,
        public string $message,
        public ?string $path = null,
        public array $context = [],
    ) {
        if (preg_match('/^CONFIG-[0-9]{3}$/', $code) !== 1) {
            throw new InvalidArgumentException(sprintf('Configuration diagnostic code "%s" is invalid.', $code));
        }

        if (trim($message) === '') {
            throw new InvalidArgumentException('Configuration diagnostic message cannot be empty.');
        }
    }
}
