<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Environment\Contracts\MutableEnvironmentInterface;

interface EnvironmentAwareApplicationInterface extends ConfigurationAwareApplicationInterface
{
    public function variables(): MutableEnvironmentInterface;
}
