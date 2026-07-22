<?php

declare(strict_types=1);

namespace Sif\Builder\Generator\ReferenceGraph;

final readonly class ReferenceGraphEdgeView
{
    public function __construct(
        public string $source,
        public string $target,
        public string $type,
        public ?int $line = null,
    ) {
    }

    /** @return array<string, int|string|null> */
    public function toArray(): array
    {
        return [
            'source' => $this->source,
            'target' => $this->target,
            'type' => $this->type,
            'line' => $this->line,
        ];
    }
}
