<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Value;

use Sif\Foundation\Http\Exceptions\InvalidHttpProtocolVersionException;

enum HttpProtocolVersion: string
{
    case Http10 = '1.0';
    case Http11 = '1.1';
    case Http2 = '2';
    case Http3 = '3';

    public static function fromString(string $version): self
    {
        $normalized = strtoupper(trim($version));
        $normalized = str_starts_with($normalized, 'HTTP/') ? substr($normalized, 5) : $normalized;

        return self::tryFrom($normalized)
            ?? throw new InvalidHttpProtocolVersionException(sprintf('Unsupported HTTP protocol version "%s".', $version));
    }
}
