<?php

declare(strict_types=1);

namespace Sif\Builder\Generator\ReferenceReport;

final readonly class DocumentReferenceView
{
    public function __construct(
        public string $identifier,
        public string $title,
        public string $documentType,
        public int $incoming,
        public int $outgoing,
        public int $brokenOutgoing,
    ) {
    }

    public function totalConnections(): int
    {
        return $this->incoming + $this->outgoing;
    }

    public function isIsolated(): bool
    {
        return $this->incoming === 0 && $this->outgoing === 0 && $this->brokenOutgoing === 0;
    }
}
