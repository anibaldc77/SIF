<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Metadata;

use Sif\Foundation\ErrorHandling\Contracts\FailureMetadataEnricherInterface;

final readonly class CompositeFailureMetadataEnricher implements FailureMetadataEnricherInterface
{
    /** @var list<FailureMetadataEnricherInterface> */
    private array $enrichers;

    /** @param iterable<FailureMetadataEnricherInterface> $enrichers */
    public function __construct(iterable $enrichers = [])
    {
        $this->enrichers = is_array($enrichers) ? array_values($enrichers) : iterator_to_array($enrichers, false);
    }

    public function enrich(array $metadata): array
    {
        foreach ($this->enrichers as $enricher) {
            $metadata = $enricher->enrich($metadata);
        }
        return $metadata;
    }
}
