<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration;

use Sif\Foundation\Configuration\Exceptions\ConfigurationNotFoundException;
use LogicException;

final readonly class ConfigurationLookupResult
{
    private function __construct(
        private ConfigurationKey $key,
        private bool $found,
        private mixed $value,
    ) {
    }

    public static function found(ConfigurationKey $key, mixed $value): self
    {
        return new self($key, true, $value);
    }

    public static function missing(ConfigurationKey $key): self
    {
        return new self($key, false, null);
    }

    public function key(): ConfigurationKey
    {
        return $this->key;
    }

    public function isFound(): bool
    {
        return $this->found;
    }

    public function isMissing(): bool
    {
        return !$this->found;
    }

    public function value(): mixed
    {
        if (!$this->found) {
            throw new LogicException(sprintf(
                'Configuration lookup result for "%s" is missing.',
                $this->key->value(),
            ));
        }

        return $this->value;
    }

    public function valueOr(mixed $default): mixed
    {
        return $this->found ? $this->value : $default;
    }

    public function require(): mixed
    {
        if (!$this->found) {
            throw ConfigurationNotFoundException::forKey($this->key->value());
        }

        return $this->value;
    }
}
