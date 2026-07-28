<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Secrets\Contracts;

use Sif\Foundation\Configuration\ConfigurationKey;

interface ConfigurationRedactionPolicyInterface
{
    public function redact(ConfigurationKey $key, mixed $value): mixed;
}
