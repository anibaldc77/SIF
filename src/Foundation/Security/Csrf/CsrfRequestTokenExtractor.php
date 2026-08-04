<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Csrf;

use JsonException;
use Sif\Foundation\Contracts\RequestInterface;

final readonly class CsrfRequestTokenExtractor
{
    public function __construct(private CsrfConfiguration $configuration = new CsrfConfiguration())
    {
    }

    public function extract(RequestInterface $request): ?string
    {
        $headers = $request->headers()->values($this->configuration->headerName());
        if ($headers !== []) {
            $value = trim($headers[0]);
            return $value === '' ? null : $value;
        }

        $body = $request->body();
        if ($body->isEmpty()) {
            return null;
        }

        $mediaType = strtolower((string) $body->mediaType());
        if ($mediaType === 'application/x-www-form-urlencoded') {
            parse_str($body->contents(), $parsed);
            $value = $parsed[$this->configuration->bodyField()] ?? null;
            return is_string($value) && $value !== '' ? $value : null;
        }

        if ($mediaType === 'application/json') {
            try {
                $parsed = json_decode($body->contents(), true, 16, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                return null;
            }
            if (!is_array($parsed)) {
                return null;
            }
            $value = $parsed[$this->configuration->bodyField()] ?? null;
            return is_string($value) && $value !== '' ? $value : null;
        }

        return null;
    }
}
