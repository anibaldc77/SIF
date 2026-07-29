<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer;

use Sif\Foundation\Installer\Exceptions\InvalidInstallationOptionException;

final readonly class InstallationOption
{
    private string $name;

    private string|int|float|bool|null $value;

    private bool $sensitive;

    public function __construct(
        string $name,
        string|int|float|bool|null $value,
        bool $sensitive = false,
    ) {
        $name = strtolower(trim($name));
        if (
            $name === ''
            || strlen($name) > 128
            || preg_match('/^[a-z][a-z0-9._-]*$/D', $name) !== 1
        ) {
            throw new InvalidInstallationOptionException(
                sprintf('Invalid installation option name "%s".', $name),
            );
        }

        if (is_float($value) && !is_finite($value)) {
            throw new InvalidInstallationOptionException(
                sprintf('Installation option "%s" must contain a finite number.', $name),
            );
        }

        if (is_string($value) && strlen($value) > 8192) {
            throw new InvalidInstallationOptionException(
                sprintf('Installation option "%s" exceeds the maximum string length.', $name),
            );
        }

        $this->name = $name;
        $this->value = $value;
        $this->sensitive = $sensitive;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value(): string|int|float|bool|null
    {
        return $this->value;
    }

    public function sensitive(): bool
    {
        return $this->sensitive;
    }

    public function diagnosticValue(): string|int|float|bool|null
    {
        return $this->sensitive ? '[REDACTED]' : $this->value;
    }

    /** @return array{name: string, value: string|int|float|bool|null, sensitive: bool} */
    public function summary(): array
    {
        return [
            'name' => $this->name,
            'value' => $this->diagnosticValue(),
            'sensitive' => $this->sensitive,
        ];
    }
}
