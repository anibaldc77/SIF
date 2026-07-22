<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration\Cli;

use Sif\Builder\Analyzer\RepositoryPolicy\RepositoryPolicySet;
use Sif\Builder\Configuration\Profile\ResolvedBuildProfile;

final readonly class ResolvedCliConfiguration
{
    public function __construct(
        public ResolvedBuildProfile $profile,
        public RepositoryPolicySet $policies,
        public ?string $sourcePath,
    ) {
    }
}
