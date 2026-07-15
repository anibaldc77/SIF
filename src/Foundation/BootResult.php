<?php

declare(strict_types=1);

namespace Sif\Foundation;

use DateTimeImmutable;
use InvalidArgumentException;
use Sif\Foundation\DTO\BootError;
use Sif\Foundation\DTO\BootWarning;
use Sif\Foundation\Exceptions\InvalidBootResultException;

final readonly class BootResult
{
    /**
     * @param list<BootError> $errors
     * @param list<BootWarning> $warnings
     */
    private function __construct(
        private bool $success,
        private BootStage $stage,
        private DateTimeImmutable $startedAt,
        private DateTimeImmutable $finishedAt,
        private array $errors,
        private array $warnings,
        private ?\Throwable $cause,
    ) {
        if ($finishedAt < $startedAt) {
            throw new InvalidArgumentException('Finish time cannot precede start time.');
        }
    }

    /** @param list<BootWarning> $warnings */
    public static function success(
        BootStage $stage,
        DateTimeImmutable $startedAt,
        DateTimeImmutable $finishedAt,
        array $warnings = [],
    ): self {
        return new self(true, $stage, $startedAt, $finishedAt, [], $warnings, null);
    }

    /**
     * @param list<BootError> $errors
     * @param list<BootWarning> $warnings
     */
    public static function failure(
        BootStage $stage,
        DateTimeImmutable $startedAt,
        DateTimeImmutable $finishedAt,
        array $errors,
        ?\Throwable $cause = null,
        array $warnings = [],
    ): self {
        if ($errors === []) {
            throw InvalidBootResultException::missingErrors();
        }

        return new self(false, $stage, $startedAt, $finishedAt, $errors, $warnings, $cause);
    }

    public function succeeded(): bool { return $this->success; }
    public function failed(): bool { return !$this->success; }
    public function stage(): BootStage { return $this->stage; }
    public function startedAt(): DateTimeImmutable { return $this->startedAt; }
    public function finishedAt(): DateTimeImmutable { return $this->finishedAt; }

    public function durationMilliseconds(): float
    {
        return ((float) $this->finishedAt->format('U.u') - (float) $this->startedAt->format('U.u')) * 1000;
    }

    /** @return list<BootError> */
    public function errors(): array { return $this->errors; }

    /** @return list<BootWarning> */
    public function warnings(): array { return $this->warnings; }

    public function cause(): ?\Throwable { return $this->cause; }
}
