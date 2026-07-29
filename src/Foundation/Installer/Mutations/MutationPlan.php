<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer\Mutations;

use Sif\Foundation\Installer\Exceptions\DuplicateMutationDescriptorException;
use Sif\Foundation\Installer\Exceptions\InvalidMutationPlanException;

final readonly class MutationPlan
{
    /** @var list<MutationDescriptor> */
    private array $mutations;
    private string $fingerprint;

    /** @param iterable<MutationDescriptor> $mutations */
    public function __construct(iterable $mutations)
    {
        $normalized = [];
        $seen = [];
        foreach ($mutations as $mutation) {
            if (!$mutation instanceof MutationDescriptor) {
                throw new InvalidMutationPlanException('Mutation plans accept only MutationDescriptor instances.');
            }
            if (isset($seen[$mutation->identifier()])) {
                throw new DuplicateMutationDescriptorException(sprintf('Duplicate mutation descriptor "%s".', $mutation->identifier()));
            }
            $seen[$mutation->identifier()] = true;
            $normalized[] = $mutation;
        }

        $this->mutations = $normalized;
        $json = json_encode($this->summary(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
        $this->fingerprint = hash('sha256', $json);
    }

    /** @return list<MutationDescriptor> */
    public function mutations(): array { return $this->mutations; }

    /** @return list<array<string, mixed>> */
    public function summary(): array { return array_map(static fn (MutationDescriptor $mutation): array => $mutation->summary(), $this->mutations); }
    public function fingerprint(): string { return $this->fingerprint; }
}
