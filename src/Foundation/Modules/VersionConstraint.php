<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules;

use Sif\Foundation\Modules\Exceptions\InvalidVersionConstraintException;

final readonly class VersionConstraint
{
    /** @var list<callable(ModuleVersion): bool> */
    private array $predicates;

    public function __construct(private string $value)
    {
        if ($value === '' || trim($value) !== $value || preg_match('/\s/', $value) === 1) {
            throw InvalidVersionConstraintException::forValue($value);
        }

        $predicates = [];
        foreach (explode(',', $value) as $expression) {
            if ($expression === '') {
                throw InvalidVersionConstraintException::forValue($value);
            }
            foreach ($this->compile($expression) as $predicate) {
                $predicates[] = $predicate;
            }
        }
        $this->predicates = $predicates;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function matches(ModuleVersion $version): bool
    {
        foreach ($this->predicates as $predicate) {
            if (!$predicate($version)) {
                return false;
            }
        }
        return true;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /** @return list<callable(ModuleVersion): bool> */
    private function compile(string $expression): array
    {
        if ($expression === '*') {
            return [static fn (ModuleVersion $version): bool => true];
        }

        if (preg_match('/^(\^|~)(.+)$/D', $expression, $matches) === 1) {
            $minimum = $this->version($matches[2]);
            $maximum = $matches[1] === '^' ? $this->caretMaximum($minimum) : $this->tildeMaximum($minimum);
            return [
                static fn (ModuleVersion $version): bool => $version->compareTo($minimum) >= 0,
                static fn (ModuleVersion $version): bool => $version->compareTo($maximum) < 0,
            ];
        }

        if (preg_match('/^(>=|<=|>|<|=)?(.+)$/D', $expression, $matches) !== 1) {
            throw InvalidVersionConstraintException::forValue($this->value);
        }

        $operator = $matches[1] !== '' ? $matches[1] : '=';
        $candidate = $matches[2];
        if (preg_match('/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(\*|x|X)$/D', $candidate, $wildcard) === 1) {
            if ($operator !== '=') {
                throw InvalidVersionConstraintException::forValue($this->value);
            }
            $major = (int) $wildcard[1];
            $minor = (int) $wildcard[2];
            return [static fn (ModuleVersion $version): bool => $version->major() === $major && $version->minor() === $minor];
        }
        if (preg_match('/^(0|[1-9]\d*)\.(\*|x|X)$/D', $candidate, $wildcard) === 1) {
            if ($operator !== '=') {
                throw InvalidVersionConstraintException::forValue($this->value);
            }
            $major = (int) $wildcard[1];
            return [static fn (ModuleVersion $version): bool => $version->major() === $major];
        }

        $expected = $this->version($candidate);
        return [static function (ModuleVersion $version) use ($operator, $expected): bool {
            $comparison = $version->compareTo($expected);
            return match ($operator) {
                '=' => $comparison === 0,
                '>' => $comparison > 0,
                '>=' => $comparison >= 0,
                '<' => $comparison < 0,
                '<=' => $comparison <= 0,
            };
        }];
    }

    private function version(string $value): ModuleVersion
    {
        try {
            return new ModuleVersion($value);
        } catch (\Throwable) {
            throw InvalidVersionConstraintException::forValue($this->value);
        }
    }

    private function caretMaximum(ModuleVersion $minimum): ModuleVersion
    {
        if ($minimum->major() > 0) {
            return new ModuleVersion(($minimum->major() + 1) . '.0.0');
        }
        if ($minimum->minor() > 0) {
            return new ModuleVersion('0.' . ($minimum->minor() + 1) . '.0');
        }
        return new ModuleVersion('0.0.' . ($minimum->patch() + 1));
    }

    private function tildeMaximum(ModuleVersion $minimum): ModuleVersion
    {
        return new ModuleVersion($minimum->major() . '.' . ($minimum->minor() + 1) . '.0');
    }
}
