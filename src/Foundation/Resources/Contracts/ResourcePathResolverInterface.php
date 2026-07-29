<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Contracts;

use Sif\Foundation\Resources\Filesystem\ResolvedResourcePath;
use Sif\Foundation\Resources\ResourcePath;
use Sif\Foundation\Resources\ResourceRootIdentifier;

interface ResourcePathResolverInterface
{
    public function resolve(ResourceRootIdentifier $root, ResourcePath $path): ResolvedResourcePath;
}
