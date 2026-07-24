<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Configuration\Contracts\MutableConfigurationInterface;

/** Extended application contract exposing the Runtime configuration repository. */
interface ConfigurationAwareApplicationInterface extends CapabilityAwareApplicationInterface
{
    public function configuration(): MutableConfigurationInterface;
}
