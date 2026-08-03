<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Controller;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Context\ContextId;
use Sif\Foundation\Context\ExecutionContext;
use Sif\Foundation\Contracts\ActionServiceResolverInterface;
use Sif\Foundation\Controller\Argument\ActionArgumentDefinition;
use Sif\Foundation\Controller\Argument\ActionArgumentResolver;
use Sif\Foundation\Controller\Argument\ActionArgumentSource;
use Sif\Foundation\Controller\Argument\ActionArgumentType;
use Sif\Foundation\Controller\Input\JsonRequestBodyParser;
use Sif\Foundation\Http\Value\AttributeBag;
use Sif\Foundation\Http\Value\CookieBag;
use Sif\Foundation\Http\Value\HeaderBag;
use Sif\Foundation\Http\Value\HttpMethod;
use Sif\Foundation\Http\Value\QueryParameterBag;
use Sif\Foundation\Http\Value\Request;
use Sif\Foundation\Http\Value\RequestBody;
use Sif\Foundation\Http\Value\Uri;

final class ControllerArgumentResolutionTest extends TestCase
{
    public function testResolvesEachSourceWithoutAmbiguousPrecedence(): void
    {
        $request = new Request(
            HttpMethod::Post,
            new Uri(path: '/users/42'),
            headers: new HeaderBag(['X-Mode' => 'strict']),
            cookies: new CookieBag(['session' => 'cookie-value']),
            query: new QueryParameterBag(['id' => '99', 'active' => 'yes']),
            attributes: new AttributeBag([
                'route.parameters' => ['id' => '42'],
                'tenant' => 'acme',
            ]),
            body: new RequestBody('{"name":"Ana"}', 'application/json', 'utf-8'),
        );

        $resolver = new ActionArgumentResolver(new JsonRequestBodyParser());
        $result = $resolver->resolve([
            new ActionArgumentDefinition('id', ActionArgumentSource::Route, ActionArgumentType::Integer),
            new ActionArgumentDefinition('queryId', ActionArgumentSource::Query, ActionArgumentType::Integer, 'id'),
            new ActionArgumentDefinition('name', ActionArgumentSource::Body, ActionArgumentType::String),
            new ActionArgumentDefinition('mode', ActionArgumentSource::Header, ActionArgumentType::String, 'X-Mode'),
            new ActionArgumentDefinition('session', ActionArgumentSource::Cookie, ActionArgumentType::String),
            new ActionArgumentDefinition('tenant', ActionArgumentSource::Attribute, ActionArgumentType::String),
            new ActionArgumentDefinition('active', ActionArgumentSource::Query, ActionArgumentType::Boolean),
        ], $request);

        self::assertTrue($result->successful());
        self::assertSame([42, 99, 'Ana', 'strict', 'cookie-value', 'acme', true], $result->arguments());
    }

    public function testDistinguishesMissingExplicitNullAndDefault(): void
    {
        $request = new Request(
            HttpMethod::Get,
            new Uri(path: '/'),
            query: new QueryParameterBag(['nullable' => null]),
        );

        $result = (new ActionArgumentResolver())->resolve([
            new ActionArgumentDefinition('nullable', ActionArgumentSource::Query, ActionArgumentType::String, nullable: true),
            new ActionArgumentDefinition('optional', ActionArgumentSource::Query, ActionArgumentType::String, required: false),
            new ActionArgumentDefinition('page', ActionArgumentSource::Query, ActionArgumentType::Integer, required: false, hasDefault: true, defaultValue: 1),
        ], $request);

        self::assertTrue($result->successful());
        self::assertSame([null, null, 1], $result->arguments());
    }

    public function testReportsMissingAndConversionFailuresWithoutThrowing(): void
    {
        $request = new Request(
            HttpMethod::Get,
            new Uri(path: '/'),
            query: new QueryParameterBag(['limit' => 'many']),
        );

        $result = (new ActionArgumentResolver())->resolve([
            new ActionArgumentDefinition('id', ActionArgumentSource::Route, ActionArgumentType::Integer),
            new ActionArgumentDefinition('limit', ActionArgumentSource::Query, ActionArgumentType::Integer),
        ], $request);

        self::assertFalse($result->successful());
        self::assertSame(['argument.missing', 'argument.conversion_failed'], array_map(
            static fn ($issue): string => $issue->code(),
            $result->issues(),
        ));
    }

    public function testResolvesRequestContextAndExplicitService(): void
    {
        $request = new Request(HttpMethod::Get, new Uri(path: '/'));
        $context = new ExecutionContext(
            new ContextId('ctx-1'),
            new ContextId('corr-1'),
            new DateTimeImmutable('2026-08-02T00:00:00+00:00'),
        );
        $service = new \stdClass();
        $services = new class($service) implements ActionServiceResolverInterface {
            public function __construct(private readonly object $service) {}
            public function has(string $identifier): bool { return $identifier === 'clock'; }
            public function resolve(string $identifier): mixed { return $this->service; }
        };

        $result = (new ActionArgumentResolver(serviceResolver: $services))->resolve([
            new ActionArgumentDefinition('request', ActionArgumentSource::Request, ActionArgumentType::Request),
            new ActionArgumentDefinition('context', ActionArgumentSource::Context, ActionArgumentType::Context),
            new ActionArgumentDefinition('clock', ActionArgumentSource::Service, ActionArgumentType::Service),
        ], $request, $context);

        self::assertTrue($result->successful());
        self::assertSame($request, $result->arguments()[0]);
        self::assertSame($context, $result->arguments()[1]);
        self::assertSame($service, $result->arguments()[2]);
    }

    public function testInvalidJsonProducesSafeStructuredIssue(): void
    {
        $request = new Request(
            HttpMethod::Post,
            new Uri(path: '/'),
            body: new RequestBody('{invalid', 'application/json'),
        );

        $result = (new ActionArgumentResolver(new JsonRequestBodyParser()))->resolve([
            new ActionArgumentDefinition('name', ActionArgumentSource::Body, ActionArgumentType::String),
        ], $request);

        self::assertFalse($result->successful());
        self::assertSame('body.parse_failed', $result->issues()[0]->code());
        self::assertSame('argument.missing', $result->issues()[1]->code());
    }
}
