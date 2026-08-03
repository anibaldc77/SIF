<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced;

use Sif\Foundation\Http\Exceptions\RouteUrlGenerationException;
use Sif\Foundation\Http\Value\Uri;

final readonly class RouteBaseUri
{
    public function __construct(private Uri $uri)
    {
        if ($uri->scheme() === '' || $uri->host() === '') {
            throw new RouteUrlGenerationException('An absolute route base URI requires scheme and host.');
        }
        if ($uri->query() !== '' || $uri->fragment() !== '') {
            throw new RouteUrlGenerationException('A route base URI cannot contain query or fragment components.');
        }
    }

    public static function fromString(string $uri): self
    {
        return new self(Uri::fromString($uri));
    }

    public function uri(): Uri
    {
        return $this->uri;
    }
}
