<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Contracts;

use Sif\Foundation\ErrorHandling\FailureId;

interface FailureIdGeneratorInterface
{
    public function generate(): FailureId;
}
