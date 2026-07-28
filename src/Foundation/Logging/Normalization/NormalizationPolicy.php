<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Normalization;

use Sif\Foundation\Logging\Exceptions\NormalizationException;

final readonly class NormalizationPolicy
{
    public function __construct(
        private int $maxDepth = 8,
        private int $maxItemsPerCollection = 100,
        private int $maxStringLength = 4096,
        private string $truncationMarker = '[truncated]',
        private string $depthMarker = '[maximum-depth-reached]',
        private string $unsupportedMarker = '[unsupported-value]',
    ) {
        if ($maxDepth < 0) {
            throw NormalizationException::because('maximum depth must be zero or greater');
        }
        if ($maxItemsPerCollection < 1) {
            throw NormalizationException::because('maximum collection size must be at least one');
        }
        if ($maxStringLength < 1) {
            throw NormalizationException::because('maximum string length must be at least one');
        }
        foreach ([$truncationMarker, $depthMarker, $unsupportedMarker] as $marker) {
            if ($marker === '') {
                throw NormalizationException::because('normalization markers must not be empty');
            }
        }
    }

    public function maxDepth(): int { return $this->maxDepth; }
    public function maxItemsPerCollection(): int { return $this->maxItemsPerCollection; }
    public function maxStringLength(): int { return $this->maxStringLength; }
    public function truncationMarker(): string { return $this->truncationMarker; }
    public function depthMarker(): string { return $this->depthMarker; }
    public function unsupportedMarker(): string { return $this->unsupportedMarker; }
}
