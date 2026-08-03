<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Http\Exceptions\InvalidHttpResponseException;
use Sif\Foundation\Http\Exceptions\ResponseAlreadyEmittedException;
use Sif\Foundation\Http\Transport\NativeRequestFactory;
use Sif\Foundation\Http\Transport\NativeResponseEmitter;
use Sif\Foundation\Http\Value\HttpMethod;
use Sif\Foundation\Http\Value\HttpStatus;
use Sif\Foundation\Http\Value\Response;
use Sif\Foundation\Http\Value\ResponseBody;

final class HttpResponseTransportTest extends TestCase
{
    public function testResponseIsImmutableAndNormalizesBodyHeaders(): void
    {
        $response = Response::text('hello');
        $changed = $response
            ->withStatus(new HttpStatus(201))
            ->withHeader('X-Request-Id', 'abc');

        self::assertSame(200, $response->status()->code());
        self::assertFalse($response->headers()->has('X-Request-Id'));
        self::assertSame(201, $changed->status()->code());
        self::assertSame('abc', $changed->headers()->line('x-request-id'));
        self::assertSame('text/plain; charset=utf-8', $changed->normalizedHeaders()->line('Content-Type'));
        self::assertSame('5', $changed->normalizedHeaders()->line('Content-Length'));
    }

    public function testJsonResponseUsesDeterministicEncoding(): void
    {
        $response = Response::json(['path' => '/users', 'name' => 'Álvaro']);

        self::assertSame('{"path":"/users","name":"Álvaro"}', $response->body()->contents());
        self::assertSame('application/json; charset=utf-8', $response->normalizedHeaders()->line('Content-Type'));
    }

    public function testHttpStatusValidatesCodeAndReasonPhrase(): void
    {
        self::assertSame('Not Found', (new HttpStatus(404))->reasonPhrase());
        self::assertFalse((new HttpStatus(204))->permitsBody());

        $this->expectException(InvalidHttpResponseException::class);
        new HttpStatus(99);
    }

    public function testNativeRequestFactoryBuildsRequestWithoutReadingGlobals(): void
    {
        $request = (new NativeRequestFactory())->create(
            [
                'REQUEST_METHOD' => 'POST',
                'SERVER_PROTOCOL' => 'HTTP/1.1',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'example.com',
                'REQUEST_URI' => '/users?active=1',
                'CONTENT_TYPE' => 'application/json; charset=utf-8',
                'HTTP_X_REQUEST_ID' => 'req-1',
                'SERVER_PORT' => 443,
            ],
            ['active' => '1'],
            ['session' => 'cookie'],
            [],
            '{"name":"Ana"}',
        );

        self::assertSame(HttpMethod::Post, $request->method());
        self::assertSame('https://example.com/users?active=1', $request->uri()->toString());
        self::assertSame('req-1', $request->headers()->line('X-Request-Id'));
        self::assertSame('application/json', $request->body()->mediaType());
        self::assertSame('utf-8', $request->body()->charset());
        self::assertSame('1', $request->query()->get('active'));
    }

    public function testNativeRequestFactoryNormalizesUploadedFiles(): void
    {
        $request = (new NativeRequestFactory())->create(
            ['REQUEST_METHOD' => 'POST', 'HTTP_HOST' => 'example.com', 'REQUEST_URI' => '/upload'],
            files: [
                'document' => [
                    'name' => 'report.pdf',
                    'type' => 'application/pdf',
                    'tmp_name' => 'C:/temp/php.tmp',
                    'size' => 42,
                    'error' => UPLOAD_ERR_OK,
                ],
            ],
        );

        $file = $request->uploadedFiles()['document'];
        self::assertFalse(is_array($file));
        self::assertSame('report.pdf', $file->clientFilename());
        self::assertTrue($file->isSuccessful());
    }

    public function testNativeEmitterSeparatesStatusHeadersAndBody(): void
    {
        $statuses = [];
        $headers = [];
        $body = '';
        $emitter = new NativeResponseEmitter(
            static function (int $code, string $reason, string $protocol) use (&$statuses): void {
                $statuses[] = [$code, $reason, $protocol];
            },
            static function (string $name, string $value, bool $replace) use (&$headers): void {
                $headers[] = [$name, $value, $replace];
            },
            static function (string $contents) use (&$body): void {
                $body .= $contents;
            },
        );

        $emitter->emit(Response::text('hello')->withAddedHeader('Set-Cookie', ['a=1', 'b=2']));

        self::assertSame([[200, 'OK', '1.1']], $statuses);
        self::assertContains(['Content-Type', 'text/plain; charset=utf-8', true], $headers);
        self::assertContains(['Content-Length', '5', true], $headers);
        self::assertContains(['Set-Cookie', 'a=1', true], $headers);
        self::assertContains(['Set-Cookie', 'b=2', false], $headers);
        self::assertSame('hello', $body);
        self::assertTrue($emitter->hasEmitted());
    }

    public function testEmitterSuppressesBodiesForNoContentStatus(): void
    {
        $body = '';
        $emitter = new NativeResponseEmitter(
            static function (): void {},
            static function (): void {},
            static function (string $contents) use (&$body): void { $body .= $contents; },
        );

        $emitter->emit(new Response(new HttpStatus(204), body: new ResponseBody('ignored')));

        self::assertSame('', $body);
    }

    public function testEmitterRejectsSecondEmission(): void
    {
        $emitter = new NativeResponseEmitter(
            static function (): void {},
            static function (): void {},
            static function (): void {},
        );
        $emitter->emit(new Response());

        $this->expectException(ResponseAlreadyEmittedException::class);
        $emitter->emit(new Response());
    }
}
