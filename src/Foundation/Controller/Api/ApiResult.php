<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Api;

use InvalidArgumentException;
use Sif\Foundation\Http\Value\HeaderBag;

final readonly class ApiResult
{
    /**
     * @param array<string, mixed> $data
     * @param list<MediaType> $mediaTypes
     */
    public function __construct(
        private array $data,
        private int $status = 200,
        private HeaderBag $headers = new HeaderBag(),
        private array $mediaTypes = [],
    ) {
        if ($status < 100 || $status > 599) {
            throw new InvalidArgumentException('API result status must be between 100 and 599.');
        }
        foreach ($mediaTypes as $mediaType) {
            if (!$mediaType instanceof MediaType) {
                throw new InvalidArgumentException('API result media types must contain MediaType values.');
            }
        }
    }

    /** @return array<string, mixed> */
    public function data(): array
    {
        return $this->data;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function headers(): HeaderBag
    {
        return $this->headers;
    }

    /** @return list<MediaType> */
    public function mediaTypes(): array
    {
        return $this->mediaTypes === [] ? [MediaType::json()] : $this->mediaTypes;
    }
}
