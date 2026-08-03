<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Controller\Api\ApiResponseFactory;
use Sif\Foundation\Controller\Api\ApiResult;
use Sif\Foundation\Controller\Api\ContentNegotiator;
use Sif\Foundation\Controller\Api\MediaType;
use Sif\Foundation\Http\Value\HttpMethod;
use Sif\Foundation\Http\Value\Request;
use Sif\Foundation\Http\Value\Uri;

final class ControllerApiResponseTest extends TestCase
{
    public function testNegotiationHonorsQualityAndSpecificity(): void
    {
        $result = (new ContentNegotiator())->negotiate(
            'application/*;q=0.5, application/json;q=0.9',
            [MediaType::problemJson(), MediaType::json()],
        );

        self::assertTrue($result->acceptable());
        self::assertSame('application/json', $result->selected()?->value());
    }

    public function testFactoryCreatesDeterministicJsonResponse(): void
    {
        $request = (new Request(HttpMethod::Get, new Uri(path: '/items')))
            ->withHeader('Accept', 'application/json');

        $response = (new ApiResponseFactory())->create(
            $request,
            new ApiResult(['z' => 1, 'a' => ['y' => 2, 'x' => 1]], 201),
        );

        self::assertSame(201, $response->status()->code());
        self::assertSame('application/json; charset=utf-8', $response->headers()->line('Content-Type'));
        self::assertSame('{"a":{"x":1,"y":2},"z":1}', $response->body()->contents());
    }

    public function testFactoryReturnsNotAcceptableProblem(): void
    {
        $request = (new Request(HttpMethod::Get, new Uri(path: '/items')))
            ->withHeader('Accept', 'text/html');

        $response = (new ApiResponseFactory())->create($request, new ApiResult(['ok' => true]));

        self::assertSame(406, $response->status()->code());
        self::assertSame('application/problem+json; charset=utf-8', $response->headers()->line('Content-Type'));
        self::assertStringContainsString('not_acceptable', $response->body()->contents());
    }

    public function testFactoryReturnsUnsupportedMediaTypeProblem(): void
    {
        $response = (new ApiResponseFactory())->unsupportedMediaType(
            'text/plain',
            [MediaType::json()],
        );

        self::assertSame(415, $response->status()->code());
        self::assertStringContainsString('unsupported_media_type', $response->body()->contents());
    }
}
