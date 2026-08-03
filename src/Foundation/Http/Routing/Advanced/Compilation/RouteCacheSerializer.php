<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced\Compilation;

use Sif\Foundation\Http\Routing\RouteDefinition;
use Sif\Foundation\Http\Routing\RouteName;
use Sif\Foundation\Http\Routing\RouteParameter;
use Sif\Foundation\Http\Value\HttpMethod;

final readonly class RouteCacheSerializer
{
    public function encode(CompiledRouteTable $table): string
    {
        $routes = [];
        foreach ($table->routes() as $route) {
            $parameters = [];
            foreach ($route->parameters() as $parameter) {
                $parameters[] = ['name' => $parameter->name(), 'pattern' => $parameter->pattern()];
            }
            $routes[] = [
                'name' => $route->name()->value(),
                'methods' => array_map(static fn (HttpMethod $method): string => $method->value, $route->methods()),
                'path' => $route->path(),
                'handler' => $route->handler(),
                'parameters' => $parameters,
                'middleware' => $route->middleware(),
            ];
        }
        return json_encode([
            'format_version' => $table->formatVersion(),
            'fingerprint' => $table->fingerprint(),
            'routes' => $routes,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n";
    }

    public function decode(string $payload): RouteCompilationResult
    {
        try {
            $data = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($data) || ($data['format_version'] ?? null) !== CompiledRouteTable::FORMAT_VERSION) {
                return $this->failure('ROUTE-CACHE-001', 'Route cache format version is unsupported.');
            }
            if (!is_string($data['fingerprint'] ?? null) || !is_array($data['routes'] ?? null)) {
                return $this->failure('ROUTE-CACHE-002', 'Route cache payload is incomplete.');
            }
            $routes = [];
            foreach ($data['routes'] as $item) {
                if (!is_array($item)) {
                    return $this->failure('ROUTE-CACHE-003', 'Route cache contains an invalid route entry.');
                }
                $methods = [];
                foreach (($item['methods'] ?? []) as $method) {
                    if (!is_string($method)) {
                        return $this->failure('ROUTE-CACHE-003', 'Route cache contains an invalid method.');
                    }
                    $methods[] = HttpMethod::from($method);
                }
                $parameters = [];
                foreach (($item['parameters'] ?? []) as $parameter) {
                    if (!is_array($parameter) || !is_string($parameter['name'] ?? null) || !is_string($parameter['pattern'] ?? null)) {
                        return $this->failure('ROUTE-CACHE-003', 'Route cache contains an invalid parameter.');
                    }
                    $parameters[] = new RouteParameter($parameter['name'], $parameter['pattern']);
                }
                $middleware = $item['middleware'] ?? [];
                if (!is_array($middleware) || !array_is_list($middleware)) {
                    return $this->failure('ROUTE-CACHE-003', 'Route cache contains invalid middleware.');
                }
                $routes[] = new RouteDefinition(
                    new RouteName((string) ($item['name'] ?? '')),
                    $methods,
                    (string) ($item['path'] ?? ''),
                    (string) ($item['handler'] ?? ''),
                    $parameters,
                    array_map(static fn (mixed $value): string => (string) $value, $middleware),
                );
            }
            $compiler = new RouteCompiler();
            $compiled = $compiler->compile($routes);
            $table = $compiled->table();
            if ($table === null || !hash_equals($data['fingerprint'], $table->fingerprint())) {
                return $this->failure('ROUTE-CACHE-004', 'Route cache fingerprint verification failed.');
            }
            return $compiled;
        } catch (\Throwable $throwable) {
            return new RouteCompilationResult(null, [new RouteDiagnostic(
                'ROUTE-CACHE-005',
                'Route cache payload could not be decoded.',
                'error',
                ['exception' => $throwable::class],
            )]);
        }
    }

    private function failure(string $code, string $message): RouteCompilationResult
    {
        return new RouteCompilationResult(null, [new RouteDiagnostic($code, $message)]);
    }
}
