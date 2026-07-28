<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Contracts;

use Sif\Foundation\ErrorHandling\Classification\ThrowableClassification;
use Sif\Foundation\ErrorHandling\FailureEnvelope;
use Sif\Foundation\ErrorHandling\FailureOrigin;
use Throwable;

interface FailureEnvelopeFactoryInterface
{
    /** @param array<string, mixed> $metadata */
    public function create(Throwable $throwable, ThrowableClassification $classification, FailureOrigin $origin, array $metadata = []): FailureEnvelope;
}
