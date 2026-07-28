<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Contracts;

use Sif\Foundation\Configuration\Source\Contracts\ConfigurationSourceInterface;
use Sif\Foundation\Modules\Contribution\ModuleConfigurationNamespace;

interface ModuleConfigurationContributionInterface
{
    public function configurationNamespace(): ModuleConfigurationNamespace;

    /** @return list<ConfigurationSourceInterface> */
    public function configurationSources(): array;
}
