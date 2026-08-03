<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Http\Exceptions\InvalidHttpHeaderException;
use Sif\Foundation\Http\Exceptions\InvalidHttpMethodException;
use Sif\Foundation\Http\Exceptions\InvalidHttpUriException;
use Sif\Foundation\Http\Value\AttributeBag;
use Sif\Foundation\Http\Value\HeaderBag;
use Sif\Foundation\Http\Value\HttpMethod;
use Sif\Foundation\Http\Value\HttpProtocolVersion;
use Sif\Foundation\Http\Value\Request;
use Sif\Foundation\Http\Value\RequestBody;
use Sif\Foundation\Http\Value\UploadedFile;
use Sif\Foundation\Http\Value\Uri;

final class HttpRequestValueModelTest extends TestCase
{
    public function testHttpMethodAndProtocolAreNormalizedAndValidated(): void
    {
        self::assertSame(HttpMethod::Get, HttpMethod::fromString(' get '));
        self::assertTrue(HttpMethod::Get->isSafe());
        self::assertTrue(HttpMethod::Put->isIdempotent());
        self::assertSame(HttpProtocolVersion::Http2, HttpProtocolVersion::fromString('HTTP/2'));

        $this->expectException(InvalidHttpMethodException::class);
        HttpMethod::fromString('BREW');
    }

    public function testUriIsImmutableAndSerializesDeterministically(): void
    {
        $uri = Uri::fromString('https://user:pass@example.com:8443/users?active=1#top');
        $changed = $uri->withPath('/accounts')->withQuery('page=2');

        self::assertSame('example.com', $uri->host());
        self::assertSame('/users', $uri->path());
        self::assertSame('https://user:pass@example.com:8443/users?active=1#top', $uri->toString());
        self::assertSame('https://user:pass@example.com:8443/accounts?page=2#top', $changed->toString());
        self::assertNotSame($uri, $changed);
    }

    public function testUriRejectsUnsafeHostAndPort(): void
    {
        $this->expectException(InvalidHttpUriException::class);
        new Uri('https', '', 'bad host', 70000);
    }

    public function testHeaderBagIsCaseInsensitiveAndPreservesOriginalName(): void
    {
        $headers = new HeaderBag(['Content-Type' => 'application/json']);
        $changed = $headers->withAdded('content-type', 'application/problem+json');

        self::assertTrue($headers->has('CONTENT-TYPE'));
        self::assertSame(['application/json'], $headers->values('content-type'));
        self::assertSame('application/json, application/problem+json', $changed->line('Content-Type'));
        self::assertSame(['Content-Type' => ['application/json']], $headers->all());
        self::assertSame(['Content-Type' => ['application/json', 'application/problem+json']], $changed->all());
    }

    public function testHeaderBagRejectsResponseSplittingValues(): void
    {
        $this->expectException(InvalidHttpHeaderException::class);
        new HeaderBag(['X-Test' => "safe\r\nInjected: yes"]);
    }

    public function testRequestBodyReportsBytesAndMetadata(): void
    {
        $body = new RequestBody('{"ok":true}', 'application/json', 'utf-8');

        self::assertSame(11, $body->length());
        self::assertSame('application/json', $body->mediaType());
        self::assertFalse($body->isEmpty());
    }

    public function testUploadedFileIsDescriptorOnly(): void
    {
        $file = new UploadedFile('report.pdf', 'application/pdf', 'C:/temp/php123.tmp', 42, UPLOAD_ERR_OK);

        self::assertSame('report.pdf', $file->clientFilename());
        self::assertSame(42, $file->size());
        self::assertTrue($file->isSuccessful());
    }

    public function testRequestWithMethodsDoNotMutateOriginal(): void
    {
        $request = new Request(HttpMethod::Get, Uri::fromString('https://example.com/users'));
        $changed = $request
            ->withHeader('Accept', 'application/json')
            ->withAttribute('route.name', 'users.index')
            ->withBody(new RequestBody('payload'));

        self::assertFalse($request->headers()->has('Accept'));
        self::assertFalse($request->attributes()->has('route.name'));
        self::assertTrue($request->body()->isEmpty());
        self::assertSame('application/json', $changed->headers()->line('accept'));
        self::assertSame('users.index', $changed->attributes()->get('route.name'));
        self::assertSame('payload', $changed->body()->contents());
    }

    public function testParameterBagsAreImmutable(): void
    {
        $bag = new AttributeBag(['request.id' => 'abc']);
        $changed = $bag->with('tenant.id', 'tenant-1')->without('request.id');

        self::assertSame('abc', $bag->get('request.id'));
        self::assertFalse($changed->has('request.id'));
        self::assertSame('tenant-1', $changed->get('tenant.id'));
    }
}
