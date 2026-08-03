<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Problem;

use InvalidArgumentException;
use Throwable;

final class ExceptionMapperRegistry
{
    /** @var array<class-string<Throwable>, ExceptionMapping> */
    private array $mappings = [];

    public function register(ExceptionMapping $mapping): void
    {
        $class = $mapping->throwableClass();
        if (isset($this->mappings[$class])) {
            throw new InvalidArgumentException(sprintf('Exception mapping "%s" is already registered.', $class));
        }
        $this->mappings[$class] = $mapping;
    }

    public function resolve(Throwable $throwable): ?ExceptionMapping
    {
        if (isset($this->mappings[$throwable::class])) {
            return $this->mappings[$throwable::class];
        }
        foreach ($this->mappings as $mapping) {
            if ($mapping->matches($throwable)) {
                return $mapping;
            }
        }

        return null;
    }

    public function count(): int { return count($this->mappings); }
}
