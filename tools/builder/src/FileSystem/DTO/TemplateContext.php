<?php
declare(strict_types=1);

namespace Sif\Builder\FileSystem\DTO;

use Sif\Builder\FileSystem\Exceptions\InvalidTemplateException;

final class TemplateContext
{
    /** @var array<string, string> */
    private array $values = [];

    public function with(string $name, string|int|float|bool $value): self
    {
        if ($name === '' || preg_match('/^[A-Za-z_][A-Za-z0-9_.-]*$/', $name) !== 1) {
            throw new InvalidTemplateException('Template variable name is invalid.');
        }
        $copy = clone $this;
        $copy->values[$name] = match (true) { is_bool($value) => $value ? 'true' : 'false', default => (string) $value };
        return $copy;
    }

    /** @return array<string, string> */
    public function values(): array { return $this->values; }
}
