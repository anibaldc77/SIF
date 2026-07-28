<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Factory;

use Sif\Foundation\ErrorHandling\Classification\ThrowableClassification;
use Sif\Foundation\ErrorHandling\Contracts\FailureClockInterface;
use Sif\Foundation\ErrorHandling\Contracts\FailureEnvelopeFactoryInterface;
use Sif\Foundation\ErrorHandling\Contracts\FailureIdGeneratorInterface;
use Sif\Foundation\ErrorHandling\Contracts\FailureMetadataEnricherInterface;
use Sif\Foundation\ErrorHandling\Contracts\FailureMetadataNormalizerInterface;
use Sif\Foundation\ErrorHandling\FailureEnvelope;
use Sif\Foundation\ErrorHandling\FailureOrigin;
use Sif\Foundation\ErrorHandling\Metadata\CompositeFailureMetadataEnricher;
use Throwable;

final readonly class FailureEnvelopeFactory implements FailureEnvelopeFactoryInterface
{
    private FailureMetadataEnricherInterface $enricher;

    public function __construct(
        private FailureIdGeneratorInterface $idGenerator,
        private FailureClockInterface $clock,
        private FailureMetadataNormalizerInterface $normalizer,
        ?FailureMetadataEnricherInterface $enricher = null,
    ) {
        $this->enricher = $enricher ?? new CompositeFailureMetadataEnricher();
    }

    public function create(Throwable $throwable, ThrowableClassification $classification, FailureOrigin $origin, array $metadata = []): FailureEnvelope
    {
        $safeMetadata = $this->normalizer->normalize($this->enricher->enrich($metadata));

        return FailureEnvelope::capture(
            $this->idGenerator->generate(),
            $this->clock,
            $classification->category(),
            $classification->severity(),
            $classification->disposition(),
            $origin,
            $throwable,
            $safeMetadata,
        );
    }
}
