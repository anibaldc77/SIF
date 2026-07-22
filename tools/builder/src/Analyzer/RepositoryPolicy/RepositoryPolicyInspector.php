<?php

declare(strict_types=1);

namespace Sif\Builder\Analyzer\RepositoryPolicy;

use Sif\Builder\Metadata\MetadataRegistry;

final readonly class RepositoryPolicyInspector
{
    /** @return list<RepositoryPolicyFinding> */
    public function inspect(MetadataRegistry $registry, RepositoryPolicySet $policies): array
    {
        $findings = [];
        foreach ($policies->all() as $policy) {
            foreach ($policy->evaluate($registry) as $finding) {
                $findings[] = $finding;
            }
        }

        usort(
            $findings,
            static fn (RepositoryPolicyFinding $left, RepositoryPolicyFinding $right): int => $left->identity() <=> $right->identity(),
        );

        return $findings;
    }
}
