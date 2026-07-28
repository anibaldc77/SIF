<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Normalization;

use BackedEnum;
use DateTimeInterface;
use Sif\Foundation\Logging\Contracts\StructuredValueNormalizerInterface;
use Stringable;
use Throwable;
use UnitEnum;

final readonly class BoundedStructuredValueNormalizer implements StructuredValueNormalizerInterface
{
    public function __construct(private NormalizationPolicy $policy = new NormalizationPolicy())
    {
    }

    public function normalize(mixed $value): null|bool|int|float|string|array
    {
        return $this->normalizeAtDepth($value, 0);
    }

    /** @return null|bool|int|float|string|array<mixed> */
    private function normalizeAtDepth(mixed $value, int $depth): null|bool|int|float|string|array
    {
        if ($depth > $this->policy->maxDepth()) {
            return $this->policy->depthMarker();
        }
        if ($value === null || is_bool($value) || is_int($value)) {
            return $value;
        }
        if (is_float($value)) {
            return is_finite($value) ? $value : (string) $value;
        }
        if (is_string($value)) {
            return $this->truncate($value);
        }
        if (is_array($value)) {
            return $this->normalizeArray($value, $depth);
        }
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d\TH:i:s.uP');
        }
        if ($value instanceof BackedEnum) {
            return $value->value;
        }
        if ($value instanceof UnitEnum) {
            return $value->name;
        }
        if ($value instanceof Throwable) {
            return [
                'type' => $value::class,
                'message' => $this->truncate($value->getMessage()),
                'code' => $value->getCode(),
            ];
        }
        if ($value instanceof Stringable) {
            try {
                return $this->truncate((string) $value);
            } catch (Throwable) {
                return $this->policy->unsupportedMarker();
            }
        }
        if (is_object($value)) {
            return sprintf('[object:%s]', $value::class);
        }
        if (is_resource($value)) {
            return sprintf('[resource:%s]', get_resource_type($value));
        }
        return $this->policy->unsupportedMarker();
    }

    /**
     * @param array<mixed> $value
     * @return array<mixed>
     */
    private function normalizeArray(array $value, int $depth): array
    {
        $normalized = [];
        $count = 0;
        foreach ($value as $key => $item) {
            if ($count >= $this->policy->maxItemsPerCollection()) {
                $normalized['__truncated__'] = $this->policy->truncationMarker();
                break;
            }
            $normalized[$key] = $this->normalizeAtDepth($item, $depth + 1);
            ++$count;
        }
        return $normalized;
    }

    private function truncate(string $value): string
    {
        if (strlen($value) <= $this->policy->maxStringLength()) {
            return $value;
        }
        return substr($value, 0, $this->policy->maxStringLength()) . $this->policy->truncationMarker();
    }
}
