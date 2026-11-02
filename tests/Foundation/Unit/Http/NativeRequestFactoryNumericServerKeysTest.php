<?php
declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Http\Transport\NativeRequestFactory;

final class NativeRequestFactoryNumericServerKeysTest extends TestCase
{
    public function testNumericServerKeysAreIgnoredWithoutLosingValidHeaders(): void
    {
        $request = (new NativeRequestFactory())->create([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/health',
            'HTTP_X_REQUEST_ID' => 'expected-request-id',
            'CONTENT_TYPE' => 'application/json',
            279967271 => 'inherited-environment-value',
            0 => ['unexpected' => 'data'],
        ]);

        self::assertSame(
            'expected-request-id',
            $request->headers()->line('X-Request-Id')
        );

        self::assertSame(
            'application/json',
            $request->headers()->line('Content-Type')
        );

        self::assertArrayNotHasKey(
            279967271,
            $request->server()->all()
        );

        self::assertArrayNotHasKey(
            0,
            $request->server()->all()
        );

        self::assertSame(
            '/health',
            $request->uri()->path()
        );
    }
}
