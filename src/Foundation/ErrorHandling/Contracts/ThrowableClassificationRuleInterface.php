<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Contracts;

use Sif\Foundation\ErrorHandling\Classification\ThrowableClassification;
use Throwable;

interface ThrowableClassificationRuleInterface
{
    public function name(): string;

    public function classify(Throwable $throwable): ?ThrowableClassification;
}
