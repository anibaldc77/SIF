<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Api;

use JsonException;
use Sif\Foundation\Controller\Api\Exceptions\ApiResponseException;

final class ApiJsonEncoder
{
    /** @param array<string, mixed> $data */
    public function encode(array $data): string
    {
        try {
            return json_encode(
                $this->normalize($data),
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            );
        } catch (JsonException $exception) {
            throw new ApiResponseException('API response data could not be serialized.', previous: $exception);
        }
    }

    private function normalize(mixed $value): mixed
    {
        if (!is_array($value)) {
            if (is_object($value) || is_resource($value)) {
                throw new ApiResponseException('Arbitrary objects and resources cannot be serialized as API data.');
            }
            return $value;
        }

        if (!array_is_list($value)) {
            ksort($value, SORT_STRING);
        }
        foreach ($value as $key => $item) {
            $value[$key] = $this->normalize($item);
        }
        return $value;
    }
}
