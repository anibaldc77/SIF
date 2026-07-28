<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Normalization;

use Sif\Foundation\Logging\Contracts\CanonicalStructuredValueSerializerInterface;
use Sif\Foundation\Logging\Contracts\SecretRedactorInterface;
use Sif\Foundation\Logging\Contracts\StructuredValueNormalizerInterface;

final readonly class NormalizedAttributes
{
    /** @var array<string, null|bool|int|float|string|array<mixed>> */
    private array $values;

    /**
     * @param array<string, mixed> $attributes
     */
    public static function fromRaw(
        array $attributes,
        StructuredValueNormalizerInterface $normalizer,
        SecretRedactorInterface $redactor,
    ): self {
        $normalized = [];
        foreach ($attributes as $key => $value) {
            $normalized[$key] = $normalizer->normalize($value);
        }
        return new self($redactor->redact($normalized));
    }

    /**
     * @param array<string, null|bool|int|float|string|array<mixed>> $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /** @return array<string, null|bool|int|float|string|array<mixed>> */
    public function values(): array
    {
        return $this->values;
    }

    public function canonical(CanonicalStructuredValueSerializerInterface $serializer): string
    {
        return $serializer->serialize($this->values);
    }
}
