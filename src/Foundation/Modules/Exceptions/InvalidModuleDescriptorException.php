<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Exceptions;

use InvalidArgumentException;

final class InvalidModuleDescriptorException extends InvalidArgumentException
{
    public static function emptyName(): self { return new self('Module name cannot be empty.'); }
    public static function selfReference(string $id): self { return new self(sprintf('Module "%s" cannot depend on or conflict with itself.', $id)); }
    public static function duplicateRelation(string $id): self { return new self(sprintf('Module relation "%s" is declared more than once.', $id)); }
    public static function contradictoryRelation(string $id): self { return new self(sprintf('Module "%s" cannot be both a dependency and a conflict.', $id)); }
}
