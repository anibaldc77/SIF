<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Environment;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Environment\ArrayEnvironmentProvider;
use Sif\Foundation\Environment\CompositeEnvironmentProvider;
use Sif\Foundation\Environment\Exceptions\InvalidEnvironmentKeyException;
use Sif\Foundation\Environment\Exceptions\InvalidEnvironmentPrecedenceException;
use Sif\Foundation\Environment\Exceptions\InvalidEnvironmentValueException;
use Sif\Foundation\Environment\NativeEnvironmentProvider;

final class EnvironmentProvidersTest extends TestCase
{
    public function testArrayProviderReadsValuesAndDefaults(): void
    {
        $provider = new ArrayEnvironmentProvider(['APP_ENV' => 'testing']);

        self::assertTrue($provider->has('APP_ENV'));
        self::assertSame('testing', $provider->get('APP_ENV'));
        self::assertSame('fallback', $provider->get('MISSING', 'fallback'));
        self::assertFalse($provider->has('MISSING'));
    }

    public function testArrayProviderNormalizesScalarValuesAndSkipsNull(): void
    {
        $provider = new ArrayEnvironmentProvider([
            'STRING' => 'value',
            'INTEGER' => 42,
            'FLOAT' => 2.5,
            'TRUE' => true,
            'FALSE' => false,
            'NULL' => null,
        ]);

        self::assertSame([
            'STRING' => 'value',
            'INTEGER' => '42',
            'FLOAT' => '2.5',
            'TRUE' => '1',
            'FALSE' => '0',
        ], $provider->all());
    }

    public function testArrayProviderRejectsEmptyKeys(): void
    {
        $this->expectException(InvalidEnvironmentKeyException::class);

        new ArrayEnvironmentProvider(['   ' => 'value']);
    }

    public function testArrayProviderRejectsNonScalarValues(): void
    {
        $this->expectException(InvalidEnvironmentValueException::class);

        new ArrayEnvironmentProvider(['INVALID' => []]);
    }

    public function testNativeProviderUsesDefaultPrecedence(): void
    {
        $provider = new NativeEnvironmentProvider(
            env: ['SHARED' => 'env', 'ENV_ONLY' => 'env'],
            server: ['SHARED' => 'server', 'SERVER_ONLY' => 'server'],
            process: ['SHARED' => 'process', 'PROCESS_ONLY' => 'process'],
        );

        self::assertSame('process', $provider->get('SHARED'));
        self::assertSame('env', $provider->get('ENV_ONLY'));
        self::assertSame('server', $provider->get('SERVER_ONLY'));
        self::assertSame('process', $provider->get('PROCESS_ONLY'));
    }

    public function testNativeProviderSupportsCustomPrecedence(): void
    {
        $provider = new NativeEnvironmentProvider(
            precedence: [
                NativeEnvironmentProvider::SOURCE_PROCESS,
                NativeEnvironmentProvider::SOURCE_ENV,
                NativeEnvironmentProvider::SOURCE_SERVER,
            ],
            env: ['SHARED' => 'env'],
            server: ['SHARED' => 'server'],
            process: ['SHARED' => 'process'],
        );

        self::assertSame('server', $provider->get('SHARED'));
    }

    public function testNativeProviderRejectsIncompletePrecedence(): void
    {
        $this->expectException(InvalidEnvironmentPrecedenceException::class);

        new NativeEnvironmentProvider(
            precedence: [NativeEnvironmentProvider::SOURCE_ENV],
            env: [],
            server: [],
            process: [],
        );
    }

    public function testNativeProviderRejectsDuplicatePrecedence(): void
    {
        $this->expectException(InvalidEnvironmentPrecedenceException::class);

        new NativeEnvironmentProvider(
            precedence: [
                NativeEnvironmentProvider::SOURCE_ENV,
                NativeEnvironmentProvider::SOURCE_ENV,
                NativeEnvironmentProvider::SOURCE_PROCESS,
            ],
            env: [],
            server: [],
            process: [],
        );
    }

    public function testCompositeProviderUsesFirstProviderWins(): void
    {
        $provider = new CompositeEnvironmentProvider(
            new ArrayEnvironmentProvider(['SHARED' => 'first', 'FIRST' => 'yes']),
            new ArrayEnvironmentProvider(['SHARED' => 'second', 'SECOND' => 'yes']),
        );

        self::assertSame('first', $provider->get('SHARED'));
        self::assertSame('yes', $provider->get('FIRST'));
        self::assertSame('yes', $provider->get('SECOND'));
    }

    public function testCompositeProviderAllMatchesLookupPrecedence(): void
    {
        $provider = new CompositeEnvironmentProvider(
            new ArrayEnvironmentProvider(['SHARED' => 'first', 'FIRST' => 'yes']),
            new ArrayEnvironmentProvider(['SHARED' => 'second', 'SECOND' => 'yes']),
        );

        self::assertSame([
            'SHARED' => 'first',
            'SECOND' => 'yes',
            'FIRST' => 'yes',
        ], $provider->all());
    }

    public function testCompositeProviderMayBeEmpty(): void
    {
        $provider = new CompositeEnvironmentProvider();

        self::assertFalse($provider->has('MISSING'));
        self::assertNull($provider->get('MISSING'));
        self::assertSame([], $provider->all());
    }
}
