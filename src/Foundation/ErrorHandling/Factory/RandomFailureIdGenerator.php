<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Factory;

use Sif\Foundation\ErrorHandling\Contracts\FailureIdGeneratorInterface;
use Sif\Foundation\ErrorHandling\FailureId;

final class RandomFailureIdGenerator implements FailureIdGeneratorInterface
{
    public function generate(): FailureId
    {
        return new FailureId('failure-' . bin2hex(random_bytes(16)));
    }
}
