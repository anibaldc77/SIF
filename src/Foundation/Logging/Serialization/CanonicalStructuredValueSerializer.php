<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Serialization;

use JsonException;
use Sif\Foundation\Logging\Contracts\CanonicalStructuredValueSerializerInterface;
use Sif\Foundation\Logging\Exceptions\CanonicalSerializationException;

final class CanonicalStructuredValueSerializer implements CanonicalStructuredValueSerializerInterface
{
    public function serialize(null|bool|int|float|string|array $value): string
    {
        try {
            return json_encode(
                $this->canonicalize($value),
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION,
            );
        } catch (JsonException $exception) {
            throw CanonicalSerializationException::fromThrowable($exception);
        }
    }

    /**
     * @param null|bool|int|float|string|array<mixed> $value
     * @return null|bool|int|float|string|array<mixed>
     */
    private function canonicalize(null|bool|int|float|string|array $value): null|bool|int|float|string|array
    {
        if (!is_array($value)) {
            return $value;
        }
        if (!array_is_list($value)) {
            ksort($value, SORT_STRING);
        }
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $value[$key] = $this->canonicalize($item);
            }
        }
        return $value;
    }
}
