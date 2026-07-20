<?php

declare(strict_types=1);

namespace Sif\Builder\Reference\Resolution;

use Sif\Builder\Reference\ReferenceCollection;
use Sif\Builder\Repository\RepositoryIndex;

interface ReferenceResolverInterface
{
    public function resolve(ReferenceCollection $references, RepositoryIndex $index): ResolutionResult;
}
