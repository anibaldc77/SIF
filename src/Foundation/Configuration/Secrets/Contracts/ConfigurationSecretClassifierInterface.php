<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Secrets\Contracts;

use Sif\Foundation\Configuration\ConfigurationKey;
use Sif\Foundation\Configuration\Secrets\ConfigurationSecretClassification;

interface ConfigurationSecretClassifierInterface
{
    public function classify(ConfigurationKey $key): ConfigurationSecretClassification;
}
