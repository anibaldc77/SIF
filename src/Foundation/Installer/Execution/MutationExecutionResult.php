<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer\Execution;

use Sif\Foundation\Installer\Exceptions\InvalidMutationExecutionResultException;

final readonly class MutationExecutionResult
{
    private string $mutationIdentifier;
    private MutationExecutionStatus $status;
    private ?string $receiptFingerprint;

    /** @var array<string, bool|float|int|string|null> */
    private array $metadata;

    /** @param array<string, bool|float|int|string|null> $metadata */
    public function __construct(
        string $mutationIdentifier,
        MutationExecutionStatus $status,
        ?string $receiptFingerprint = null,
        array $metadata = [],
    ) {
        $mutationIdentifier = strtolower(trim($mutationIdentifier));
        if ($mutationIdentifier === '' || preg_match('/^[a-z][a-z0-9._-]*$/D', $mutationIdentifier) !== 1) {
            throw new InvalidMutationExecutionResultException('Mutation execution results require a valid mutation identifier.');
        }

        if ($receiptFingerprint !== null && preg_match('/^[a-f0-9]{64}$/D', $receiptFingerprint) !== 1) {
            throw new InvalidMutationExecutionResultException('Execution receipt fingerprints must be lowercase SHA-256 values.');
        }

        ksort($metadata, SORT_STRING);
        foreach ($metadata as $key => $value) {
            if ($key === '' || preg_match('/^[a-z][a-z0-9._-]*$/D', $key) !== 1 || (is_float($value) && !is_finite($value))) {
                throw new InvalidMutationExecutionResultException(sprintf('Invalid execution result metadata entry "%s".', $key));
            }
        }

        $this->mutationIdentifier = $mutationIdentifier;
        $this->status = $status;
        $this->receiptFingerprint = $receiptFingerprint;
        $this->metadata = $metadata;
    }

    public function mutationIdentifier(): string { return $this->mutationIdentifier; }
    public function status(): MutationExecutionStatus { return $this->status; }
    public function receiptFingerprint(): ?string { return $this->receiptFingerprint; }

    /** @return array<string, mixed> */
    public function summary(): array
    {
        return [
            'mutation_identifier' => $this->mutationIdentifier,
            'status' => $this->status->value(),
            'receipt_fingerprint' => $this->receiptFingerprint,
            'metadata' => $this->metadata,
        ];
    }
}
