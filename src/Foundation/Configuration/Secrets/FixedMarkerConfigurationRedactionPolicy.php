<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Secrets;

use InvalidArgumentException;
use Sif\Foundation\Configuration\ConfigurationKey;
use Sif\Foundation\Configuration\Secrets\Contracts\ConfigurationRedactionPolicyInterface;

final readonly class FixedMarkerConfigurationRedactionPolicy implements ConfigurationRedactionPolicyInterface
{
    public function __construct(
        private string $marker = '[REDACTED]',
    ) {
        if ($this->marker === '') {
            throw new InvalidArgumentException('The configuration redaction marker must not be empty.');
        }
    }

    public function redact(ConfigurationKey $key, mixed $value): string
    {
        return $this->marker;
    }
}
