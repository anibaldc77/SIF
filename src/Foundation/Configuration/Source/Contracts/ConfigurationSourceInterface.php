<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Source\Contracts;

use Sif\Foundation\Configuration\Source\ConfigurationSourceResult;

interface ConfigurationSourceInterface
{
    public function id(): string;

    public function type(): string;

    public function precedence(): int;

    public function load(): ConfigurationSourceResult;
}
