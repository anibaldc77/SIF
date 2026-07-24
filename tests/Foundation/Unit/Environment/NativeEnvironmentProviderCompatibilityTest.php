<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Environment;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Environment\NativeEnvironmentProvider;

final class NativeEnvironmentProviderCompatibilityTest extends TestCase
{
    public function testNonScalarNativeRuntimeMetadataIsIgnored(): void
    {
        $provider = new NativeEnvironmentProvider(
            env: ['APP_ENV' => 'testing'],
            server: ['argv' => ['phpunit'], 'argc' => 1, 'SERVER_NAME' => 'localhost'],
            process: [],
        );

        self::assertFalse($provider->has('argv'));
        self::assertSame('1', $provider->get('argc'));
        self::assertSame('localhost', $provider->get('SERVER_NAME'));
        self::assertSame('testing', $provider->get('APP_ENV'));
    }

    public function testScalarNativeValuesKeepDeclaredPrecedence(): void
    {
        $provider = new NativeEnvironmentProvider(
            env: ['VALUE' => 'env'],
            server: ['VALUE' => 'server'],
            process: ['VALUE' => 'process'],
        );

        self::assertSame('process', $provider->get('VALUE'));
    }
}
