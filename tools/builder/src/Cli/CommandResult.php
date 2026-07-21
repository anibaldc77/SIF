<?php

declare(strict_types=1);

namespace Sif\Builder\Cli;

use Sif\Builder\Cli\Exception\InvalidCommandResultException;
use Sif\Builder\Engine\BuilderResult;

final readonly class CommandResult
{
    public ?string $standardOutput;
    public ?string $standardError;
    public ?string $failureSummary;

    public function __construct(
        public ExitCode $exitCode,
        ?string $standardOutput = null,
        ?string $standardError = null,
        public ?BuilderResult $builderResult = null,
        ?string $failureSummary = null,
    ) {
        $this->standardOutput = self::normalizePayload($standardOutput, 'Standard output');
        $this->standardError = self::normalizePayload($standardError, 'Standard error');
        $this->failureSummary = self::normalizePayload($failureSummary, 'Failure summary');

        if ($this->exitCode->isSuccess() && $this->failureSummary !== null) {
            throw new InvalidCommandResultException('Successful command results must not contain a failure summary.');
        }
        if (!$this->exitCode->isSuccess() && $this->failureSummary === null) {
            throw new InvalidCommandResultException('Unsuccessful command results require a safe failure summary.');
        }
    }

    public static function success(?string $standardOutput = null, ?BuilderResult $builderResult = null): self
    {
        return new self(ExitCode::SUCCESS, $standardOutput, null, $builderResult);
    }

    public static function failure(
        ExitCode $exitCode,
        string $failureSummary,
        ?string $standardError = null,
        ?string $standardOutput = null,
        ?BuilderResult $builderResult = null,
    ): self {
        if ($exitCode->isSuccess()) {
            throw new InvalidCommandResultException('Failure factory requires a non-success exit code.');
        }

        return new self(
            $exitCode,
            $standardOutput,
            $standardError ?? $failureSummary,
            $builderResult,
            $failureSummary,
        );
    }

    private static function normalizePayload(?string $payload, string $label): ?string
    {
        if ($payload === null) {
            return null;
        }
        if (str_contains($payload, "\0")) {
            throw new InvalidCommandResultException(sprintf('%s must not contain null bytes.', $label));
        }
        if ($payload === '') {
            return null;
        }

        return $payload;
    }
}
