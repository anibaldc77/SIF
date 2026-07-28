<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Metadata;

use JsonSerializable;
use Sif\Foundation\ErrorHandling\Contracts\FailureMetadataNormalizerInterface;
use Sif\Foundation\ErrorHandling\Exceptions\InvalidFailureMetadataConfigurationException;
use Stringable;
use Throwable;

final readonly class SafeFailureMetadataNormalizer implements FailureMetadataNormalizerInterface
{
    public function __construct(
        private int $maximumDepth = 6,
        private int $maximumItems = 128,
        private int $maximumStringLength = 4096,
        private string $truncationMarker = '[truncated]',
        private string $unsupportedMarker = '[unsupported]',
    ) {
        if ($maximumDepth < 1 || $maximumItems < 1 || $maximumStringLength < 1 || $truncationMarker === '' || $unsupportedMarker === '') {
            throw new InvalidFailureMetadataConfigurationException('Failure metadata limits and markers must be positive and non-empty.');
        }
    }

    public function normalize(array $metadata): array
    {
        $remaining = $this->maximumItems;
        $normalized = $this->normalizeArray($metadata, 1, $remaining);

        /** @var array<string, null|bool|int|float|string|array<mixed>> $normalized */
        return $normalized;
    }

    /** @param array<mixed> $values
     *  @return array<mixed>
     */
    private function normalizeArray(array $values, int $depth, int &$remaining): array
    {
        if ($depth > $this->maximumDepth) {
            return [$this->truncationMarker];
        }

        $result = [];
        foreach ($values as $key => $value) {
            if ($remaining <= 0) {
                $result['_truncated'] = $this->truncationMarker;
                break;
            }
            --$remaining;
            $safeKey = is_int($key) ? $key : $this->truncate($key);
            $result[$safeKey] = $this->normalizeValue($value, $depth, $remaining);
        }
        return $result;
    }

    /** @return null|bool|int|float|string|array<mixed> */
    private function normalizeValue(mixed $value, int $depth, int &$remaining): null|bool|int|float|string|array
    {
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
            return $this->normalizeArray($value, $depth + 1, $remaining);
        }
        if ($value instanceof Throwable) {
            return [
                'type' => $value::class,
                'message' => $this->truncate($value->getMessage()),
                'code' => $value->getCode(),
            ];
        }
        if ($value instanceof JsonSerializable) {
            try {
                return $this->normalizeValue($value->jsonSerialize(), $depth + 1, $remaining);
            } catch (Throwable) {
                return $this->unsupportedMarker;
            }
        }
        if ($value instanceof Stringable) {
            try {
                return $this->truncate((string) $value);
            } catch (Throwable) {
                return $this->unsupportedMarker;
            }
        }
        return $this->unsupportedMarker;
    }

    private function truncate(string $value): string
    {
        if (strlen($value) <= $this->maximumStringLength) {
            return $value;
        }
        $available = max(0, $this->maximumStringLength - strlen($this->truncationMarker));
        return substr($value, 0, $available) . $this->truncationMarker;
    }
}
