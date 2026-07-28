<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration;

use Sif\Foundation\Configuration\Exceptions\UnsupportedConfigurationValueException;

final class ConfigurationValueValidator
{
    public function assertSupported(mixed $value): void
    {
        ConfigurationValueType::fromValue($value);

        if (!is_array($value)) {
            return;
        }

        foreach ($value as $item) {
            try {
                $this->assertSupported($item);
            } catch (UnsupportedConfigurationValueException $failure) {
                throw $failure;
            }
        }
    }
}
