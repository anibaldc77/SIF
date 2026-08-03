<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced;

final readonly class RouteTransportConstraints
{
    public function __construct(
        private ?RouteHostConstraint $host = null,
        private ?RouteSchemeConstraint $scheme = null,
        private ?RoutePortConstraint $port = null,
    ) {
    }

    public function host(): ?RouteHostConstraint { return $this->host; }
    public function scheme(): ?RouteSchemeConstraint { return $this->scheme; }
    public function port(): ?RoutePortConstraint { return $this->port; }
    public function isEmpty(): bool { return $this->host === null && $this->scheme === null && $this->port === null; }

    /** @return array<string, string>|null */
    public function match(RouteRequestTarget $target): ?array
    {
        if ($this->scheme !== null && !$this->scheme->matches($target->scheme())) {
            return null;
        }
        if ($this->port !== null && !$this->port->matches($target->port(), $target->scheme())) {
            return null;
        }
        return $this->host?->match($target->host()) ?? [];
    }
}
