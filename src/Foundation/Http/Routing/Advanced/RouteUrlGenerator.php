<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced;

use Sif\Foundation\Http\Routing\RouteName;

final readonly class RouteUrlGenerator
{
    public function __construct(private NamedRouteIndex $routes)
    {
    }

    public function generate(
        string|RouteName $name,
        ?RouteUrlParameters $parameters = null,
        ?RouteBaseUri $baseUri = null,
    ): RouteUrlGenerationResult {
        $entry = $this->routes->find($name);
        if ($entry === null) {
            $value = $name instanceof RouteName ? $name->value() : $name;
            return RouteUrlGenerationResult::failed([sprintf('Route "%s" is not registered.', $value)]);
        }

        $parameters ??= new RouteUrlParameters();
        $values = $entry['defaults']->all();
        foreach ($parameters->values() as $key => $value) {
            $values[$key] = $value;
        }

        $path = $entry['route']->path();
        $issues = [];
        foreach ($entry['route']->parameters() as $parameter) {
            $nameValue = $parameter->name();
            if (!array_key_exists($nameValue, $values) || $values[$nameValue] === null) {
                $issues[] = sprintf('Required route parameter "%s" is missing.', $nameValue);
                continue;
            }
            $raw = self::scalarToString($values[$nameValue]);
            if (preg_match('~^(?:' . $parameter->pattern() . ')$~u', $raw) !== 1) {
                $issues[] = sprintf('Route parameter "%s" does not satisfy its constraint.', $nameValue);
                continue;
            }
            $path = str_replace('{' . $nameValue . '}', rawurlencode($raw), $path);
        }

        if ($issues !== []) {
            return RouteUrlGenerationResult::failed($issues);
        }

        $query = http_build_query($parameters->query(), '', '&', PHP_QUERY_RFC3986);
        $relative = $path;
        if ($query !== '') {
            $relative .= '?' . $query;
        }
        if ($parameters->fragment() !== '') {
            $relative .= '#' . rawurlencode($parameters->fragment());
        }

        if ($baseUri === null) {
            return RouteUrlGenerationResult::generated($relative);
        }

        $base = $baseUri->uri();
        $basePath = rtrim($base->path(), '/');
        $absolutePath = ($basePath === '' ? '' : $basePath) . $path;
        $uri = $base->withPath($absolutePath)->withQuery($query)->withFragment($parameters->fragment());
        return RouteUrlGenerationResult::generated($uri->toString());
    }

    private static function scalarToString(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        return (string) $value;
    }
}
