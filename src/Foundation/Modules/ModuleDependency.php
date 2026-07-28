<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules;

final readonly class ModuleDependency
{
    public function __construct(private ModuleId $moduleId, private string $constraint = '*')
    {
        if ($constraint === '' || trim($constraint) !== $constraint || preg_match('/\s/', $constraint) === 1) {
            throw new \InvalidArgumentException('Module dependency constraint must be a non-empty token without whitespace.');
        }
    }

    public function moduleId(): ModuleId { return $this->moduleId; }
    public function constraint(): string { return $this->constraint; }
}
