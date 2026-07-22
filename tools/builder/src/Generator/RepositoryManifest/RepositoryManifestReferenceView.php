<?php

declare(strict_types=1);

namespace Sif\Builder\Generator\RepositoryManifest;

final readonly class RepositoryManifestReferenceView
{
    public function __construct(
        public string $source,
        public string $target,
        public string $type,
        public ?int $line,
        public bool $resolved,
        public ?string $reason = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'source' => $this->source,
            'target' => $this->target,
            'type' => $this->type,
            'line' => $this->line,
            'resolved' => $this->resolved,
            'reason' => $this->reason,
        ];
    }
}
