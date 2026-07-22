<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration;

interface RepositoryConfigurationLoaderInterface
{
    public function load(string $repositoryRoot, ?string $configurationPath = null): ConfigurationLoadResult;
}
