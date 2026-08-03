<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Input;

use JsonException;
use Sif\Foundation\Contracts\RequestBodyParserInterface;
use Sif\Foundation\Controller\Exceptions\ControllerArgumentException;
use Sif\Foundation\Http\Value\RequestBody;

final class JsonRequestBodyParser implements RequestBodyParserInterface
{
    public function supports(RequestBody $body): bool
    {
        $mediaType = strtolower($body->mediaType() ?? '');
        return $mediaType === 'application/json' || str_ends_with($mediaType, '+json');
    }

    public function parse(RequestBody $body): array
    {
        if (!$this->supports($body)) {
            throw new ControllerArgumentException('The request body is not a supported JSON media type.');
        }

        if ($body->isEmpty()) {
            return [];
        }

        try {
            $decoded = json_decode($body->contents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new ControllerArgumentException('The request body contains invalid JSON.', previous: $exception);
        }

        if (!is_array($decoded) || array_is_list($decoded)) {
            throw new ControllerArgumentException('The JSON request body must contain an object at its root.');
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }
}
