<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Problem;

use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Controller\Exceptions\ControllerArgumentResolutionException;
use Sif\Foundation\Controller\Exceptions\ControllerValidationException;
use Throwable;

final readonly class ProblemDetailsFactory
{
    public function fromThrowable(
        Throwable $throwable,
        RequestInterface $request,
        ?ExceptionMapping $mapping = null,
        ?string $failureId = null,
    ): ProblemDetails {
        $instance = $request->uri()->path();
        if ($throwable instanceof ControllerArgumentResolutionException) {
            $issues = array_map(static fn ($issue): array => [
                'code' => $issue->code(),
                'path' => $issue->source()->value . '.' . $issue->argument(),
                'message' => $issue->message(),
                'metadata' => $issue->metadata(),
            ], $throwable->issues());

            return new ProblemDetails(
                'https://sif.dev/problems/argument-resolution',
                'Invalid controller arguments',
                400,
                'One or more controller arguments could not be resolved.',
                $instance,
                ['errors' => $issues],
            );
        }
        if ($throwable instanceof ControllerValidationException) {
            return new ProblemDetails(
                'https://sif.dev/problems/validation',
                'Validation failed',
                422,
                'The request input did not satisfy the required constraints.',
                $instance,
                ['errors' => $throwable->result()->toArray()],
            );
        }
        if ($mapping !== null) {
            return new ProblemDetails(
                $mapping->type(),
                $mapping->title(),
                $mapping->status(),
                $mapping->detail(),
                $instance,
            );
        }

        $extensions = $failureId === null ? [] : ['failure_id' => $failureId];

        return new ProblemDetails(
            'https://sif.dev/problems/internal-error',
            'Internal server error',
            500,
            'An unexpected error occurred.',
            $instance,
            $extensions,
        );
    }
}
