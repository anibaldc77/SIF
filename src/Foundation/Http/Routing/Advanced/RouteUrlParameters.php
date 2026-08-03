<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced;

use Sif\Foundation\Http\Exceptions\RouteUrlGenerationException;

final readonly class RouteUrlParameters
{
    /** @var array<string, scalar|null> */
    private array $values;
    /** @var array<string, scalar|list<scalar|null>|null> */
    private array $query;

    /**
     * @param array<string, scalar|null> $values
     * @param array<string, scalar|list<scalar|null>|null> $query
     */
    public function __construct(array $values = [], array $query = [], private string $fragment = '')
    {
        foreach (array_keys($values) as $name) {
            self::assertName($name);
        }
        foreach (array_keys($query) as $name) {
            if ($name === '' || preg_match('/[\x00-\x1F\x7F]/', $name) === 1) {
                throw new RouteUrlGenerationException('Query parameter names must be non-empty and printable.');
            }
        }
        if (preg_match('/[\x00-\x1F\x7F]/', $fragment) === 1) {
            throw new RouteUrlGenerationException('URL fragments cannot contain control characters.');
        }
        $this->values = $values;
        $this->query = $query;
    }

    /** @return array<string, scalar|null> */ public function values(): array { return $this->values; }
    /** @return array<string, scalar|list<scalar|null>|null> */ public function query(): array { return $this->query; }
    public function fragment(): string { return $this->fragment; }

    private static function assertName(string $name): void
    {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name) !== 1) {
            throw new RouteUrlGenerationException(sprintf('Invalid route URL parameter name "%s".', $name));
        }
    }
}
