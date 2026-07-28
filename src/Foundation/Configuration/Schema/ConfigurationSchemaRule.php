<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Schema;

use Sif\Foundation\Configuration\ConfigurationKey;
use Sif\Foundation\Configuration\ConfigurationValueType;
use Sif\Foundation\Configuration\Schema\Contracts\ConfigurationNormalizerInterface;

final readonly class ConfigurationSchemaRule
{
    public ConfigurationKey $key;

    public function __construct(
        string|ConfigurationKey $key,
        public ConfigurationValueType $type,
        public bool $required = true,
        public bool $nullable = false,
        public ?ConfigurationNormalizerInterface $normalizer = null,
    ) {
        $this->key = $key instanceof ConfigurationKey ? $key : new ConfigurationKey($key);
    }
}
