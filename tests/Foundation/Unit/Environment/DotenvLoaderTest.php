<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Environment;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Environment\ArrayEnvironmentProvider;
use Sif\Foundation\Environment\DotenvEnvironmentProvider;
use Sif\Foundation\Environment\DotenvParser;
use Sif\Foundation\Environment\Exceptions\DotenvSourceNotFoundException;
use Sif\Foundation\Environment\Exceptions\InvalidDotenvSyntaxException;
use Sif\Foundation\Environment\Exceptions\UnresolvedEnvironmentVariableException;

final class DotenvLoaderTest extends TestCase
{
    public function testParsesAssignmentsCommentsAndBlankLines(): void
    {
        $provider = DotenvEnvironmentProvider::fromString(<<<'ENV'
# application environment
APP_ENV=testing

APP_DEBUG=true # local override
URL=https://example.test/#fragment
ENV);

        self::assertSame('testing', $provider->get('APP_ENV'));
        self::assertSame('true', $provider->get('APP_DEBUG'));
        self::assertSame('https://example.test/#fragment', $provider->get('URL'));
    }

    public function testSupportsExportPrefix(): void
    {
        $provider = DotenvEnvironmentProvider::fromString('export APP_ENV=production');

        self::assertSame('production', $provider->get('APP_ENV'));
    }

    public function testSingleQuotedValuesRemainLiteral(): void
    {
        $provider = DotenvEnvironmentProvider::fromString(<<<'ENV'
BASE=value
LITERAL='${BASE}\n'
ENV);

        self::assertSame('${BASE}\n', $provider->get('LITERAL'));
    }

    public function testDoubleQuotedValuesInterpretEscapesAndExpansion(): void
    {
        $provider = DotenvEnvironmentProvider::fromString(<<<'ENV'
NAME=SIF
MESSAGE="Hello ${NAME}\nRuntime"
ENV);

        self::assertSame("Hello SIF\nRuntime", $provider->get('MESSAGE'));
    }

    public function testUnquotedValuesExpandPreviouslyResolvedVariables(): void
    {
        $provider = DotenvEnvironmentProvider::fromString(<<<'ENV'
HOST=localhost
PORT=8080
URL=http://${HOST}:${PORT}
ENV);

        self::assertSame('http://localhost:8080', $provider->get('URL'));
    }

    public function testExpansionUsesFallbackProvider(): void
    {
        $fallback = new ArrayEnvironmentProvider(['HOST' => 'runtime.local']);
        $provider = DotenvEnvironmentProvider::fromString('URL=https://${HOST}', $fallback);

        self::assertSame('https://runtime.local', $provider->get('URL'));
    }

    public function testExpansionSupportsDefaultValues(): void
    {
        $provider = DotenvEnvironmentProvider::fromString('PORT=${APP_PORT:-8080}');

        self::assertSame('8080', $provider->get('PORT'));
    }

    public function testExpansionRejectsUnresolvedVariables(): void
    {
        $this->expectException(UnresolvedEnvironmentVariableException::class);

        DotenvEnvironmentProvider::fromString('URL=https://${HOST}');
    }

    public function testRejectsMissingAssignmentOperator(): void
    {
        $this->expectException(InvalidDotenvSyntaxException::class);

        (new DotenvParser())->parse('APP_ENV');
    }

    public function testRejectsInvalidVariableNames(): void
    {
        $this->expectException(InvalidDotenvSyntaxException::class);

        (new DotenvParser())->parse('APP-ENV=testing');
    }

    public function testRejectsUnterminatedSingleQuotedValues(): void
    {
        $this->expectException(InvalidDotenvSyntaxException::class);

        (new DotenvParser())->parse("APP_ENV='testing");
    }

    public function testRejectsUnexpectedContentAfterQuotedValue(): void
    {
        $this->expectException(InvalidDotenvSyntaxException::class);

        (new DotenvParser())->parse('APP_ENV="testing" invalid');
    }

    public function testLoadsValuesFromFile(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'sif-env-');
        self::assertIsString($path);

        try {
            file_put_contents($path, "APP_ENV=testing\n");
            $provider = DotenvEnvironmentProvider::fromFile($path);

            self::assertSame('testing', $provider->get('APP_ENV'));
        } finally {
            @unlink($path);
        }
    }

    public function testMissingFileFailsExplicitly(): void
    {
        $this->expectException(DotenvSourceNotFoundException::class);

        DotenvEnvironmentProvider::fromFile(sys_get_temp_dir() . '/missing-sif-environment-file');
    }

    public function testProviderExposesImmutableSnapshot(): void
    {
        $provider = DotenvEnvironmentProvider::fromString("A=one\nB=two");
        $values = $provider->all();
        $values['A'] = 'changed';

        self::assertSame('one', $provider->get('A'));
        self::assertSame(['A' => 'one', 'B' => 'two'], $provider->all());
    }
}
