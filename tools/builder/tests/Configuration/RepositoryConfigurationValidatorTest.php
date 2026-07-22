<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Configuration;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Configuration\RepositoryConfigurationValidator;

final class RepositoryConfigurationValidatorTest extends TestCase
{
    public function testBuildsImmutableConfigurationFromValidPayload(): void
    {
        $result = (new RepositoryConfigurationValidator())->validate([
            'schema_version' => '1.0',
            'default_profile' => 'development',
            'profiles' => [
                'development' => [
                    'analyzers' => ['metadata.completeness'],
                    'generators' => [],
                    'reporters' => ['report.markdown'],
                    'execution' => ['strict' => false],
                ],
            ],
        ], '/repo/.sif/builder.json');

        self::assertTrue($result->isSuccessful());
        self::assertSame('development', $result->configuration?->defaultProfile);
        self::assertSame('/repo/.sif/builder.json', $result->configuration?->sourcePath);
    }

    public function testReportsMissingRequiredFieldsDeterministically(): void
    {
        $result = (new RepositoryConfigurationValidator())->validate([]);

        self::assertSame(
            ['CONFIG-104', 'CONFIG-104', 'CONFIG-104'],
            array_map(static fn ($diagnostic): string => $diagnostic->code, $result->diagnostics),
        );
    }

    public function testRejectsUnsupportedSchemaAndUnknownDefaultProfile(): void
    {
        $validator = new RepositoryConfigurationValidator();

        self::assertSame('CONFIG-103', $validator->validate([
            'schema_version' => '2.0',
            'default_profile' => 'default',
            'profiles' => ['default' => []],
        ])->diagnostics[0]->code);

        self::assertSame('CONFIG-106', $validator->validate([
            'schema_version' => '1.0',
            'default_profile' => 'release',
            'profiles' => ['development' => []],
        ])->diagnostics[0]->code);
    }
}
