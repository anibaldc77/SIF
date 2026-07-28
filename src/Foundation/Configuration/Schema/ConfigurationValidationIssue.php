<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Schema;

use Sif\Foundation\Configuration\ConfigurationKey;

final readonly class ConfigurationValidationIssue
{
    public function __construct(
        public string $code,
        public string $message,
        public ConfigurationKey $key,
    ) {
        if (trim($code) === '') {
            throw new \InvalidArgumentException('Validation issue code must not be empty.');
        }

        if (trim($message) === '') {
            throw new \InvalidArgumentException('Validation issue message must not be empty.');
        }
    }
}
