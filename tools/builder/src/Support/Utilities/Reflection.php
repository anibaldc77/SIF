<?php
declare(strict_types=1);

namespace Sif\Support\Utilities;

use ReflectionClass;
use Sif\Support\Exceptions\InvalidArgumentException;

final class Reflection
{
    public function className(object|string $subject): string { return is_object($subject) ? $subject::class : $subject; }
    public function hasMethod(object|string $subject, string $method): bool { return (new ReflectionClass($this->className($subject)))->hasMethod($method); }
    public function instantiate(string $class, mixed ...$arguments): object { try { return (new ReflectionClass($class))->newInstanceArgs($arguments); } catch (\ReflectionException $exception) { throw new InvalidArgumentException("Class '$class' cannot be instantiated.", 0, $exception); } }
}
