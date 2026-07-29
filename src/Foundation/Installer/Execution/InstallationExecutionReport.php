<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer\Execution;

use Sif\Foundation\Installer\Exceptions\InvalidInstallationExecutionReportException;

final readonly class InstallationExecutionReport
{
    private string $planFingerprint;
    private bool $successful;
    private MutationJournal $journal;
    private ?string $failedMutationIdentifier;
    private bool $rollbackAttempted;
    private bool $rollbackCompleted;

    public function __construct(
        string $planFingerprint,
        bool $successful,
        MutationJournal $journal,
        ?string $failedMutationIdentifier,
        bool $rollbackAttempted,
        bool $rollbackCompleted,
    ) {
        if (preg_match('/^[a-f0-9]{64}$/D', $planFingerprint) !== 1) {
            throw new InvalidInstallationExecutionReportException('Execution reports require a lowercase SHA-256 plan fingerprint.');
        }
        if ($successful && $failedMutationIdentifier !== null) {
            throw new InvalidInstallationExecutionReportException('Successful execution reports cannot identify a failed mutation.');
        }
        if (!$successful && $failedMutationIdentifier === null) {
            throw new InvalidInstallationExecutionReportException('Failed execution reports must identify the failed mutation.');
        }
        if ($rollbackCompleted && !$rollbackAttempted) {
            throw new InvalidInstallationExecutionReportException('Rollback cannot be complete when it was not attempted.');
        }

        $this->planFingerprint = $planFingerprint;
        $this->successful = $successful;
        $this->journal = $journal;
        $this->failedMutationIdentifier = $failedMutationIdentifier;
        $this->rollbackAttempted = $rollbackAttempted;
        $this->rollbackCompleted = $rollbackCompleted;
    }

    public function planFingerprint(): string { return $this->planFingerprint; }
    public function isSuccessful(): bool { return $this->successful; }
    public function journal(): MutationJournal { return $this->journal; }
    public function failedMutationIdentifier(): ?string { return $this->failedMutationIdentifier; }
    public function rollbackAttempted(): bool { return $this->rollbackAttempted; }
    public function rollbackCompleted(): bool { return $this->rollbackCompleted; }

    /** @return array<string, mixed> */
    public function summary(): array
    {
        return [
            'plan_fingerprint' => $this->planFingerprint,
            'successful' => $this->successful,
            'failed_mutation_identifier' => $this->failedMutationIdentifier,
            'rollback_attempted' => $this->rollbackAttempted,
            'rollback_completed' => $this->rollbackCompleted,
            'journal' => $this->journal->summary(),
        ];
    }
}
