<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Manifest;

use Sif\Foundation\ApplicationSkeleton\Exceptions\InvalidProjectManifestException;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectPath;

final readonly class ProjectEntryPoint
{
    public function __construct(
        private string $name,
        private ProjectPath $path,
    ) {
        if (preg_match('/^[a-z][a-z0-9]*(?:-[a-z0-9]+)*$/', $name) !== 1) {
            throw new InvalidProjectManifestException(
                sprintf('Invalid entry-point name "%s".', $name),
            );
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function path(): ProjectPath
    {
        return $this->path;
    }

    /** @return array{name: string, path: string} */
    public function toArray(): array
    {
        return ['name' => $this->name, 'path' => $this->path->value()];
    }
}
