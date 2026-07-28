<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Configuration;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Configuration\ConfigurationKey;
use Sif\Foundation\Configuration\ImmutableConfigurationRepository;
use Sif\Foundation\Configuration\Secrets\CompositeConfigurationSecretClassifier;
use Sif\Foundation\Configuration\Secrets\ConfigurationSecretClassification;
use Sif\Foundation\Configuration\Secrets\ExplicitConfigurationSecretClassifier;
use Sif\Foundation\Configuration\Secrets\FixedMarkerConfigurationRedactionPolicy;
use Sif\Foundation\Configuration\Secrets\SafeConfigurationExporter;

final class ConfigurationSecretRedactionTest extends TestCase
{
    public function testExactKeysAndSubtreesCanBeClassifiedAsSecret(): void
    {
        $classifier = new ExplicitConfigurationSecretClassifier(
            ['database.password'],
            ['services.mail.credentials'],
        );

        self::assertSame(
            ConfigurationSecretClassification::Secret,
            $classifier->classify(new ConfigurationKey('database.password')),
        );
        self::assertSame(
            ConfigurationSecretClassification::Secret,
            $classifier->classify(new ConfigurationKey('services.mail.credentials.username')),
        );
        self::assertSame(
            ConfigurationSecretClassification::Public,
            $classifier->classify(new ConfigurationKey('database.host')),
        );
    }

    public function testSafeExportRedactsSecretsAndPreservesPublicStructure(): void
    {
        $configuration = new ImmutableConfigurationRepository([
            'app' => ['name' => 'SIF'],
            'database' => [
                'host' => 'localhost',
                'password' => 'not-for-display',
            ],
            'services' => [
                'mail' => [
                    'credentials' => [
                        'username' => 'mailer',
                        'password' => 'also-secret',
                    ],
                ],
            ],
        ]);
        $exporter = new SafeConfigurationExporter(
            new ExplicitConfigurationSecretClassifier(
                ['database.password'],
                ['services.mail.credentials'],
            ),
        );

        $export = $exporter->export($configuration);

        self::assertSame('SIF', $export->values()['app']['name']);
        self::assertSame('localhost', $export->values()['database']['host']);
        self::assertSame('[REDACTED]', $export->values()['database']['password']);
        self::assertSame('[REDACTED]', $export->values()['services']['mail']['credentials']);
        self::assertSame(
            ['database.password', 'services.mail.credentials'],
            $export->redactedKeys(),
        );
        self::assertTrue($export->containsRedactions());
        self::assertSame('not-for-display', $configuration->string('database.password'));
    }

    public function testCustomMarkerPolicyDoesNotReceiveOrExposeValuesInResultMetadata(): void
    {
        $export = (new SafeConfigurationExporter(
            new ExplicitConfigurationSecretClassifier(['token']),
            new FixedMarkerConfigurationRedactionPolicy('***'),
        ))->export(new ImmutableConfigurationRepository(['token' => 'secret-value']));

        self::assertSame(['token' => '***'], $export->values());
        self::assertSame(['token'], $export->redactedKeys());
        self::assertNotContains('secret-value', $export->redactedKeys());
    }

    public function testCompositeClassifierTreatsAnySecretClassificationAsSecret(): void
    {
        $classifier = new CompositeConfigurationSecretClassifier([
            new ExplicitConfigurationSecretClassifier(['database.password']),
            new ExplicitConfigurationSecretClassifier(['api.token']),
        ]);

        self::assertTrue($classifier->classify(new ConfigurationKey('api.token'))->isSecret());
        self::assertTrue($classifier->classify(new ConfigurationKey('database.password'))->isSecret());
        self::assertFalse($classifier->classify(new ConfigurationKey('app.name'))->isSecret());
    }

    public function testExportWithoutSecretMatchesReturnsUnmodifiedSafeCopy(): void
    {
        $configuration = new ImmutableConfigurationRepository(['app' => ['name' => 'SIF']]);
        $export = (new SafeConfigurationExporter(
            new ExplicitConfigurationSecretClassifier(),
        ))->export($configuration);

        self::assertSame($configuration->all(), $export->values());
        self::assertSame([], $export->redactedKeys());
        self::assertFalse($export->containsRedactions());
    }
}
