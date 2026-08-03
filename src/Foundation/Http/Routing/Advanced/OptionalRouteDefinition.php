<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced;

use Sif\Foundation\Http\Exceptions\RoutePrecedenceException;
use Sif\Foundation\Http\Routing\RouteDefinition;
use Sif\Foundation\Http\Routing\RouteName;
use Sif\Foundation\Http\Routing\RouteParameter;
use Sif\Foundation\Http\Value\HttpMethod;

final readonly class OptionalRouteDefinition
{
    /** @var list<HttpMethod> */
    private array $methods;

    /** @var array<string, RouteParameter> */
    private array $parameters;

    /** @var list<string> */
    private array $middleware;

    /**
     * @param list<HttpMethod> $methods
     * @param list<RouteParameter> $parameters
     * @param list<string> $middleware
     */
    public function __construct(
        private RouteName $name,
        array $methods,
        private string $path,
        private string $handler,
        array $parameters = [],
        array $middleware = [],
        private int $priority = 0,
    ) {
        if (preg_match('~/\{([A-Za-z_][A-Za-z0-9_]*)\?\}$~', $path, $match) !== 1) {
            throw new RoutePrecedenceException('Optional route parameters must occupy one trailing path segment.');
        }
        if (substr_count($path, '?}') !== 1 || str_contains(substr($path, 0, -strlen($match[0])), '?}')) {
            throw new RoutePrecedenceException('Only one trailing optional route parameter is supported.');
        }

        $normalized = [];
        foreach ($parameters as $parameter) {
            $normalized[$parameter->name()] = $parameter;
        }
        if (!isset($normalized[$match[1]])) {
            throw new RoutePrecedenceException(sprintf('Optional parameter "%s" must be declared.', $match[1]));
        }

        $this->methods = array_values($methods);
        $this->parameters = $normalized;
        $this->middleware = array_values($middleware);

        // Validate the full variant using the existing route model.
        new RouteDefinition($name, $this->methods, str_replace('?', '', $path), $handler, array_values($normalized), $this->middleware);
    }

    public function name(): RouteName { return $this->name; }
    /** @return list<HttpMethod> */ public function methods(): array { return $this->methods; }
    public function path(): string { return $this->path; }
    public function handler(): string { return $this->handler; }
    /** @return array<string, RouteParameter> */ public function parameters(): array { return $this->parameters; }
    /** @return list<string> */ public function middleware(): array { return $this->middleware; }
    public function priority(): int { return $this->priority; }

    /** @return list<RouteDefinition> */
    public function expand(): array
    {
        if (preg_match('~/\{([A-Za-z_][A-Za-z0-9_]*)\?\}$~', $this->path, $match) !== 1) {
            throw new RoutePrecedenceException('Optional route definition became invalid.');
        }
        $optional = $match[1];
        $fullPath = str_replace('?', '', $this->path);
        $shortPath = substr($this->path, 0, -strlen($match[0]));
        if ($shortPath === '') {
            $shortPath = '/';
        }

        $requiredParameters = $this->parameters;
        unset($requiredParameters[$optional]);

        return [
            new RouteDefinition($this->name, $this->methods, $fullPath, $this->handler, array_values($this->parameters), $this->middleware),
            new RouteDefinition($this->name, $this->methods, $shortPath, $this->handler, array_values($requiredParameters), $this->middleware),
        ];
    }
}
