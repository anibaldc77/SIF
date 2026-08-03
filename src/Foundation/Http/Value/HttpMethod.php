<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Value;

use Sif\Foundation\Http\Exceptions\InvalidHttpMethodException;

enum HttpMethod: string
{
    case Get = 'GET';
    case Head = 'HEAD';
    case Post = 'POST';
    case Put = 'PUT';
    case Patch = 'PATCH';
    case Delete = 'DELETE';
    case Options = 'OPTIONS';
    case Trace = 'TRACE';
    case Connect = 'CONNECT';

    public static function fromString(string $method): self
    {
        $normalized = strtoupper(trim($method));

        return self::tryFrom($normalized)
            ?? throw new InvalidHttpMethodException(sprintf('Unsupported HTTP method "%s".', $method));
    }

    public function isSafe(): bool
    {
        return in_array($this, [self::Get, self::Head, self::Options, self::Trace], true);
    }

    public function isIdempotent(): bool
    {
        return $this->isSafe() || in_array($this, [self::Put, self::Delete], true);
    }
}
