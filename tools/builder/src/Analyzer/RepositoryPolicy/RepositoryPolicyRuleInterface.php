<?php

declare(strict_types=1);

namespace Sif\Builder\Analyzer\RepositoryPolicy;

use Sif\Builder\Metadata\MetadataRegistry;

interface RepositoryPolicyRuleInterface
{
    public function id(): string;

    /** @return list<RepositoryPolicyFinding> */
    public function evaluate(MetadataRegistry $registry): array;
}
