<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Http;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Http\Cookie\Cookie;
use Sif\Foundation\Http\Cookie\CookieCollection;
use Sif\Foundation\Http\Cookie\CookieExpiration;
use Sif\Foundation\Http\Cookie\CookieName;
use Sif\Foundation\Http\Cookie\CookieSameSite;
use Sif\Foundation\Http\Cookie\CookieSerializer;
use Sif\Foundation\Http\Cookie\CookieValue;
use Sif\Foundation\Http\Cookie\Exceptions\InvalidCookieException;

final class CookieModelSerializationTest extends TestCase
{
    public function testCookieIsImmutableAndSerializesDeterministically(): void
    {
        $cookie = new Cookie(
            new CookieName('session'),
            new CookieValue('abc123'),
            '/app',
            'example.test',
            true,
            true,
            CookieSameSite::Strict,
            new CookieExpiration(
                new DateTimeImmutable('2030-01-02 03:04:05', new DateTimeZone('UTC')),
                3600,
            ),
        );
        $changed = $cookie->withValue(new CookieValue('next'));

        self::assertSame('abc123', $cookie->value()->value());
        self::assertSame('next', $changed->value()->value());
        self::assertSame(
            'session=abc123; Expires=Wed, 02 Jan 2030 03:04:05 GMT; Max-Age=3600; Domain=example.test; Path=/app; Secure; HttpOnly; SameSite=Strict',
            (new CookieSerializer())->serialize($cookie),
        );
    }

    public function testRemovalCookieExpiresImmediately(): void
    {
        self::assertSame(
            'session=; Expires=Thu, 01 Jan 1970 00:00:00 GMT; Max-Age=0; Path=/; HttpOnly; SameSite=Lax',
            (new CookieSerializer())->serialize(Cookie::removal('session')),
        );
    }

    public function testCookiePrefixAndSameSiteSecurityRulesAreEnforced(): void
    {
        $secure = new Cookie(
            new CookieName('__Host-session'),
            new CookieValue('token'),
            secure: true,
            sameSite: CookieSameSite::None,
        );
        self::assertTrue($secure->secure());

        $this->expectException(InvalidCookieException::class);
        new Cookie(new CookieName('__Secure-session'), new CookieValue('token'));
    }

    public function testInvalidValueRejectsHeaderInjection(): void
    {
        $this->expectException(InvalidCookieException::class);
        new CookieValue("safe\r\nSet-Cookie: injected=1");
    }

    public function testCollectionPreservesOrderAndProducesSeparateHeaderValues(): void
    {
        $collection = new CookieCollection([
            Cookie::create('first', '1'),
            Cookie::create('second', '2'),
        ]);
        $changed = $collection->with(Cookie::create('third', '3'));

        self::assertCount(2, $collection);
        self::assertSame(
            [
                'first=1; Path=/; HttpOnly; SameSite=Lax',
                'second=2; Path=/; HttpOnly; SameSite=Lax',
                'third=3; Path=/; HttpOnly; SameSite=Lax',
            ],
            $changed->serialized(),
        );
    }
}
