<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Value;

use Sif\Foundation\Cli\Exceptions\InvalidCliDefinitionException;

final readonly class CliOperationalClass
{
    private const VALUES = ['inspection', 'validation', 'planning', 'mutation', 'maintenance'];
    private string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));
        if (!in_array($value, self::VALUES, true)) {
            throw new InvalidCliDefinitionException(sprintf('Invalid CLI operational class "%s".', $value));
        }
        $this->value = $value;
    }

    public static function inspection(): self { return new self('inspection'); }
    public static function validation(): self { return new self('validation'); }
    public static function planning(): self { return new self('planning'); }
    public static function mutation(): self { return new self('mutation'); }
    public static function maintenance(): self { return new self('maintenance'); }
    public function value(): string { return $this->value; }
    public function mutatesState(): bool { return in_array($this->value, ['mutation', 'maintenance'], true); }
}
