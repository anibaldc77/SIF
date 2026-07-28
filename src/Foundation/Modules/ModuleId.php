<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules;

use Sif\Foundation\Modules\Exceptions\InvalidModuleIdException;

final readonly class ModuleId
{
    public function __construct(private string $value)
    {
        if ($value === '' || preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*$/D', $value) !== 1) {
            throw InvalidModuleIdException::forValue($value);
        }
    }

    public function value(): string { return $this->value; }
    public function equals(self $other): bool { return $this->value === $other->value; }
    public function __toString(): string { return $this->value; }
}
