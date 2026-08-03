<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Http\Routing\Advanced\Compilation\CompiledRouteMatcher;
use Sif\Foundation\Http\Routing\Advanced\Compilation\RouteCacheSerializer;
use Sif\Foundation\Http\Routing\Advanced\Compilation\RouteCompiler;
use Sif\Foundation\Http\Routing\RouteDefinition;
use Sif\Foundation\Http\Routing\RouteName;
use Sif\Foundation\Http\Routing\RouteParameter;
use Sif\Foundation\Http\Value\HttpMethod;

final class RouteCompilationCacheDiagnosticsTest extends TestCase
{
    public function testCompilationProducesStableFingerprintAndMatcher(): void
    {
        $route = new RouteDefinition(new RouteName('users.show'), [HttpMethod::Get], '/users/{id}', 'users.show', [new RouteParameter('id', '\\d+')]);
        $compiler = new RouteCompiler();
        $first = $compiler->compile([$route]);
        $second = $compiler->compile([$route]);
        self::assertTrue($first->successful());
        self::assertSame($first->table()?->fingerprint(), $second->table()?->fingerprint());
        $table = $first->table();
        self::assertNotNull($table);
        self::assertTrue((new CompiledRouteMatcher($table))->match(HttpMethod::Get, '/users/25')->isMatched());
    }

    public function testCacheRoundTripVerifiesFingerprint(): void
    {
        $route = new RouteDefinition(new RouteName('health'), [HttpMethod::Get], '/health', 'health');
        $table = (new RouteCompiler())->compile([$route])->table();
        self::assertNotNull($table);
        $serializer = new RouteCacheSerializer();
        $decoded = $serializer->decode($serializer->encode($table));
        self::assertTrue($decoded->successful());
        self::assertSame($table->fingerprint(), $decoded->table()?->fingerprint());
    }

    public function testTamperedCacheFailsClosed(): void
    {
        $route = new RouteDefinition(new RouteName('health'), [HttpMethod::Get], '/health', 'health');
        $table = (new RouteCompiler())->compile([$route])->table();
        self::assertNotNull($table);
        $serializer = new RouteCacheSerializer();
        $payload = str_replace('/health', '/changed', $serializer->encode($table));
        $decoded = $serializer->decode($payload);
        self::assertFalse($decoded->successful());
        self::assertSame('ROUTE-CACHE-004', $decoded->diagnostics()[0]->code());
    }

    public function testAmbiguousRoutesProduceStructuredDiagnostic(): void
    {
        $a = new RouteDefinition(new RouteName('users.id'), [HttpMethod::Get], '/users/{id}', 'a', [new RouteParameter('id')]);
        $b = new RouteDefinition(new RouteName('users.slug'), [HttpMethod::Get], '/users/{slug}', 'b', [new RouteParameter('slug')]);
        $result = (new RouteCompiler())->compile([$a, $b]);
        self::assertFalse($result->successful());
        self::assertSame('ROUTE-COMPILE-001', $result->diagnostics()[0]->code());
    }
}
