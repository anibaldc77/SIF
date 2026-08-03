<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced;

use Sif\Foundation\Http\Exceptions\RouteTransportConstraintException;

final readonly class RouteSchemeConstraint
{
    private string $scheme;

    public function __construct(string $scheme)
    {
        $normalized = strtolower($scheme);
        if (!in_array($normalized, ['http', 'https'], true)) {
            throw new RouteTransportConstraintException(sprintf('Unsupported route scheme "%s".', $scheme));
        }
        $this->scheme = $normalized;
    }

    public function value(): string { return $this->scheme; }
    public function matches(string $scheme): bool { return strtolower($scheme) === $this->scheme; }
}
