<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Configuration;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Configuration\ConfigurationValueType;
use Sif\Foundation\Configuration\ImmutableConfigurationRepository;
use Sif\Foundation\Configuration\Schema\ConfigurationSchema;
use Sif\Foundation\Configuration\Schema\ConfigurationSchemaRule;
use Sif\Foundation\Configuration\Schema\ConfigurationSchemaValidator;
use Sif\Foundation\Configuration\Schema\Normalization\LowercaseStringNormalizer;
use Sif\Foundation\Configuration\Schema\Normalization\TrimStringNormalizer;

final class ConfigurationSchemaValidationTest extends TestCase
{
    public function testValidConfigurationIsNormalizedIntoNewImmutableRepository(): void
    {
        $configuration = new ImmutableConfigurationRepository([
            'app' => ['name' => '  SIF  ', 'environment' => 'PRODUCTION'],
            'workers' => 4,
        ]);
        $schema = new ConfigurationSchema([
            new ConfigurationSchemaRule('app.name', ConfigurationValueType::String, normalizer: new TrimStringNormalizer()),
            new ConfigurationSchemaRule('app.environment', ConfigurationValueType::String, normalizer: new LowercaseStringNormalizer()),
            new ConfigurationSchemaRule('workers', ConfigurationValueType::Integer),
        ]);

        $result = (new ConfigurationSchemaValidator())->validate($configuration, $schema);

        self::assertTrue($result->isValid());
        self::assertSame([], $result->issues());
        self::assertSame('SIF', $result->repository()->string('app.name'));
        self::assertSame('production', $result->repository()->string('app.environment'));
        self::assertSame('  SIF  ', $configuration->string('app.name'));
    }

    public function testMissingRequiredKeyProducesStructuredIssue(): void
    {
        $result = (new ConfigurationSchemaValidator())->validate(
            new ImmutableConfigurationRepository(),
            new ConfigurationSchema([
                new ConfigurationSchemaRule('database.host', ConfigurationValueType::String),
            ]),
        );

        self::assertFalse($result->isValid());
        self::assertSame('CFG_SCHEMA_REQUIRED_KEY_MISSING', $result->issues()[0]->code);
        self::assertSame('database.host', $result->issues()[0]->key->value());
    }

    public function testTypeMismatchIsReportedWithoutCoercion(): void
    {
        $result = (new ConfigurationSchemaValidator())->validate(
            new ImmutableConfigurationRepository(['workers' => '4']),
            new ConfigurationSchema([
                new ConfigurationSchemaRule('workers', ConfigurationValueType::Integer),
            ]),
        );

        self::assertFalse($result->isValid());
        self::assertSame('CFG_SCHEMA_TYPE_MISMATCH', $result->issues()[0]->code);
        self::assertSame('4', $result->repository()->string('workers'));
    }

    public function testOptionalAndNullableRulesAreAccepted(): void
    {
        $result = (new ConfigurationSchemaValidator())->validate(
            new ImmutableConfigurationRepository(['description' => null]),
            new ConfigurationSchema([
                new ConfigurationSchemaRule('description', ConfigurationValueType::String, nullable: true),
                new ConfigurationSchemaRule('optional.value', ConfigurationValueType::String, required: false),
            ]),
        );

        self::assertTrue($result->isValid());
        self::assertNull($result->repository()->require('description'));
    }

    public function testDuplicateRulesAreRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ConfigurationSchema([
            new ConfigurationSchemaRule('app.name', ConfigurationValueType::String),
            new ConfigurationSchemaRule('app.name', ConfigurationValueType::String),
        ]);
    }
}
