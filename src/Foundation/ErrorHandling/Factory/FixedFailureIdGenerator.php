<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Factory;

use Sif\Foundation\ErrorHandling\Contracts\FailureIdGeneratorInterface;
use Sif\Foundation\ErrorHandling\FailureId;

final readonly class FixedFailureIdGenerator implements FailureIdGeneratorInterface
{
    public function __construct(private FailureId $id)
    {
    }

    public function generate(): FailureId
    {
        return $this->id;
    }
}
