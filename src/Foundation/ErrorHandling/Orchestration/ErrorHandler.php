<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Orchestration;

use InvalidArgumentException;
use Sif\Foundation\ErrorHandling\Contracts\ErrorHandlerInterface;
use Sif\Foundation\ErrorHandling\FailureOrigin;
use Sif\Foundation\ErrorHandling\Planning\ErrorHandlingPlan;
use Throwable;

final readonly class ErrorHandler implements ErrorHandlerInterface
{
    public function __construct(private ErrorHandlingPlan $plan)
    {
    }

    /** @param array<string, mixed> $metadata */
    public function handle(Throwable $throwable, FailureOrigin $origin, array $metadata = [], int $attempt = 1): ErrorHandlingResult
    {
        if ($attempt < 1) {
            throw new InvalidArgumentException('The recovery attempt must be greater than zero.');
        }

        $classification = $this->plan->classifier()->classify($throwable);
        $envelope = $this->plan->envelopeFactory()->create($throwable, $classification, $origin, $metadata);
        $decision = $this->plan->recoveryDecider()->decide($classification, $attempt);
        $reporting = $this->plan->reporterDispatcher()->dispatch($envelope, $decision);

        return new ErrorHandlingResult($classification, $envelope, $decision, $reporting);
    }

    public function plan(): ErrorHandlingPlan
    {
        return $this->plan;
    }
}
