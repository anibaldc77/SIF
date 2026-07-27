<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Container\ContainerValidationReport;

interface ContainerValidatorInterface
{
    public function validate(): ContainerValidationReport;
}
