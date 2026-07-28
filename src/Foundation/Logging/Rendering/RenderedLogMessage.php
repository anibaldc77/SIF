<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Rendering;

final readonly class RenderedLogMessage
{
    /** @param list<string> $unresolvedPlaceholders */
    public function __construct(
        private string $template,
        private string $rendered,
        private array $unresolvedPlaceholders = [],
    ) {
    }

    public function template(): string
    {
        return $this->template;
    }

    public function rendered(): string
    {
        return $this->rendered;
    }

    /** @return list<string> */
    public function unresolvedPlaceholders(): array
    {
        return $this->unresolvedPlaceholders;
    }

    public function isComplete(): bool
    {
        return $this->unresolvedPlaceholders === [];
    }

    public function __toString(): string
    {
        return $this->rendered;
    }
}
