<?php

declare(strict_types=1);

namespace Sif\Builder\Reference\Resolution;

use Sif\Builder\Reference\ReferenceCollection;
use Sif\Builder\Repository\RepositoryIndex;

final class ReferenceResolver implements ReferenceResolverInterface
{
    public function resolve(ReferenceCollection $references, RepositoryIndex $index): ResolutionResult
    {
        $resolved = [];
        $broken = [];

        foreach ($references->all() as $reference) {
            $target = $index->get($reference->targetIdentifier);

            if ($target === null) {
                $broken[] = new BrokenReference($reference);
                continue;
            }

            $resolved[] = new ResolvedReference($reference, $target);
        }

        return new ResolutionResult($resolved, $broken);
    }
}
