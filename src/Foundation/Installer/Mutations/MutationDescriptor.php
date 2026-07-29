<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer\Mutations;

use Sif\Foundation\Installer\AuthorizedInstallationTarget;
use Sif\Foundation\Installer\Exceptions\InvalidMutationDescriptorException;
use Sif\Foundation\Installer\MutationClassification;
use Sif\Foundation\Installer\OverwritePolicy;
use Sif\Foundation\Installer\RollbackPolicy;

final readonly class MutationDescriptor
{
    private string $identifier;
    private string $operation;
    private MutationClassification $classification;
    private ?AuthorizedInstallationTarget $target;
    private OverwritePolicy $overwritePolicy;
    private RollbackPolicy $rollbackPolicy;
    private ?string $contentFingerprint;
    private ?string $expectedCurrentFingerprint;

    /** @var array<string, bool|float|int|string|null> */
    private array $metadata;

    /** @param array<string, bool|float|int|string|null> $metadata */
    public function __construct(
        string $identifier,
        string $operation,
        MutationClassification $classification,
        ?AuthorizedInstallationTarget $target,
        OverwritePolicy $overwritePolicy,
        RollbackPolicy $rollbackPolicy,
        ?string $contentFingerprint = null,
        ?string $expectedCurrentFingerprint = null,
        array $metadata = [],
    ) {
        $identifier = strtolower(trim($identifier));
        if ($identifier === '' || strlen($identifier) > 128 || preg_match('/^[a-z][a-z0-9._-]*$/D', $identifier) !== 1) {
            throw new InvalidMutationDescriptorException(sprintf('Invalid mutation identifier "%s".', $identifier));
        }

        $operation = strtolower(trim($operation));
        if ($operation === '' || strlen($operation) > 64 || preg_match('/^[a-z][a-z0-9-]*$/D', $operation) !== 1) {
            throw new InvalidMutationDescriptorException(sprintf('Invalid mutation operation "%s".', $operation));
        }

        if ($classification->equals(MutationClassification::filesystem()) && $target === null) {
            throw new InvalidMutationDescriptorException('Filesystem mutations require an authorized target.');
        }

        if ($overwritePolicy->requiresExpectedFingerprint() && $expectedCurrentFingerprint === null) {
            throw new InvalidMutationDescriptorException('The if-unchanged overwrite policy requires an expected current fingerprint.');
        }

        foreach ([$contentFingerprint, $expectedCurrentFingerprint] as $fingerprint) {
            if ($fingerprint !== null && preg_match('/^[a-f0-9]{64}$/D', $fingerprint) !== 1) {
                throw new InvalidMutationDescriptorException('Mutation fingerprints must be lowercase SHA-256 values.');
            }
        }

        ksort($metadata, SORT_STRING);
        foreach ($metadata as $key => $value) {
            if ($key === '' || preg_match('/^[a-z][a-z0-9._-]*$/D', $key) !== 1 || (is_float($value) && !is_finite($value))) {
                throw new InvalidMutationDescriptorException(sprintf('Invalid mutation metadata entry "%s".', $key));
            }
        }

        $this->identifier = $identifier;
        $this->operation = $operation;
        $this->classification = $classification;
        $this->target = $target;
        $this->overwritePolicy = $overwritePolicy;
        $this->rollbackPolicy = $rollbackPolicy;
        $this->contentFingerprint = $contentFingerprint;
        $this->expectedCurrentFingerprint = $expectedCurrentFingerprint;
        $this->metadata = $metadata;
    }

    public function identifier(): string { return $this->identifier; }
    public function operation(): string { return $this->operation; }
    public function classification(): MutationClassification { return $this->classification; }
    public function target(): ?AuthorizedInstallationTarget { return $this->target; }
    public function overwritePolicy(): OverwritePolicy { return $this->overwritePolicy; }
    public function rollbackPolicy(): RollbackPolicy { return $this->rollbackPolicy; }
    public function contentFingerprint(): ?string { return $this->contentFingerprint; }
    public function expectedCurrentFingerprint(): ?string { return $this->expectedCurrentFingerprint; }

    /** @return array<string, mixed> */
    public function summary(): array
    {
        return [
            'identifier' => $this->identifier,
            'operation' => $this->operation,
            'classification' => $this->classification->value(),
            'target' => $this->target?->summary(),
            'overwrite_policy' => $this->overwritePolicy->value(),
            'rollback_policy' => $this->rollbackPolicy->value(),
            'content_fingerprint' => $this->contentFingerprint,
            'expected_current_fingerprint' => $this->expectedCurrentFingerprint,
            'metadata' => $this->metadata,
        ];
    }
}
