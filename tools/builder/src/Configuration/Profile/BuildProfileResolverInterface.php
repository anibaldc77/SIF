<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration\Profile;

use Sif\Builder\Configuration\RepositoryConfiguration;

interface BuildProfileResolverInterface
{
    public function resolve(
        RepositoryConfiguration $configuration,
        ?string $profileIdentifier = null,
    ): BuildProfileResolutionResult;
}
