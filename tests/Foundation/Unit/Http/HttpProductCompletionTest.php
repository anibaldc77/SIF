<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Bootstrap;
use Sif\Foundation\Contracts\HeaderBagInterface;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\Contracts\UriInterface;
use Sif\Foundation\Environment;
use Sif\Foundation\Http\Value\HeaderBag;
use Sif\Foundation\Http\Value\HttpMethod;
use Sif\Foundation\Http\Value\Request;
use Sif\Foundation\Http\Value\Response;
use Sif\Foundation\Http\Value\Uri;

final class HttpProductCompletionTest extends TestCase
{
    public function testHttpRuntimeRemainsOptionalForExistingApplications(): void
    {
        $application = (new Bootstrap())->createApplication(Environment::testing());

        self::assertNull($application->http());
    }

    public function testPublicHttpValueObjectsHonorTheirContracts(): void
    {
        $uri = new Uri(scheme: 'https', host: 'example.test', path: '/health');
        $headers = new HeaderBag(['Accept' => 'application/json']);
        $request = new Request(HttpMethod::Get, $uri, headers: $headers);
        $response = Response::json(['status' => 'ok']);

        self::assertInstanceOf(UriInterface::class, $uri);
        self::assertInstanceOf(HeaderBagInterface::class, $headers);
        self::assertInstanceOf(RequestInterface::class, $request);
        self::assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testHttpResponsesRemainTransportNeutralUntilEmission(): void
    {
        $response = Response::text('ready')
            ->withHeader('X-SIF-HTTP', 'complete');

        self::assertSame(200, $response->status()->code());
        self::assertSame('ready', $response->body()->contents());
        self::assertSame('complete', $response->headers()->line('X-SIF-HTTP'));
        self::assertSame('5', $response->normalizedHeaders()->line('Content-Length'));
    }
}
