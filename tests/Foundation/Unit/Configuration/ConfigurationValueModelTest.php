<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Configuration;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Configuration\ConfigurationKey;
use Sif\Foundation\Configuration\ConfigurationLookupResult;
use Sif\Foundation\Configuration\ConfigurationValueType;
use Sif\Foundation\Configuration\ConfigurationValueValidator;
use Sif\Foundation\Configuration\Exceptions\InvalidConfigurationKeyException;
use Sif\Foundation\Configuration\Exceptions\UnsupportedConfigurationValueException;

final class ConfigurationValueModelTest extends TestCase
{
    public function testConfigurationKeyNormalizesAndExposesSegments(): void
    {
        $key = new ConfigurationKey('  database.primary.host  ');

        self::assertSame('database.primary.host', $key->value());
        self::assertSame(['database', 'primary', 'host'], $key->segments());
        self::assertTrue($key->equals(new ConfigurationKey('database.primary.host')));
    }

    public function testConfigurationKeyRejectsEmptySegments(): void
    {
        $this->expectException(InvalidConfigurationKeyException::class);

        new ConfigurationKey('database..host');
    }

    public function testValueTypesAreDetected(): void
    {
        self::assertSame(ConfigurationValueType::Null, ConfigurationValueType::fromValue(null));
        self::assertSame(ConfigurationValueType::Boolean, ConfigurationValueType::fromValue(true));
        self::assertSame(ConfigurationValueType::Integer, ConfigurationValueType::fromValue(1));
        self::assertSame(ConfigurationValueType::Float, ConfigurationValueType::fromValue(1.5));
        self::assertSame(ConfigurationValueType::String, ConfigurationValueType::fromValue('value'));
        self::assertSame(ConfigurationValueType::Array, ConfigurationValueType::fromValue([]));
    }

    public function testValidatorRejectsNestedUnsupportedValue(): void
    {
        $this->expectException(UnsupportedConfigurationValueException::class);

        (new ConfigurationValueValidator())->assertSupported([
            'service' => new \stdClass(),
        ]);
    }

    public function testLookupResultDistinguishesNullFromMissing(): void
    {
        $key = new ConfigurationKey('nullable');
        $found = ConfigurationLookupResult::found($key, null);
        $missing = ConfigurationLookupResult::missing($key);

        self::assertTrue($found->isFound());
        self::assertNull($found->value());
        self::assertTrue($missing->isMissing());
        self::assertSame('fallback', $missing->valueOr('fallback'));
    }
}
