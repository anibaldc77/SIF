<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Publication;

final readonly class CompiledResourcePublicationPlan
{
    /** @param list<PlannedResourcePublication> $publications */
    public function __construct(
        private array $publications,
        private ImmutableResourceManifest $manifest,
    ) {
    }

    /** @return list<PlannedResourcePublication> */
    public function publications(): array { return $this->publications; }
    public function manifest(): ImmutableResourceManifest { return $this->manifest; }
    public function count(): int { return count($this->publications); }
}
