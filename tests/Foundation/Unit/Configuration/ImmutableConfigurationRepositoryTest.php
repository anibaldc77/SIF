<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Configuration;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Configuration\ConfigurationKey;
use Sif\Foundation\Configuration\Exceptions\ConfigurationNotFoundException;
use Sif\Foundation\Configuration\Exceptions\ConfigurationTypeMismatchException;
use Sif\Foundation\Configuration\ImmutableConfigurationRepository;

final class ImmutableConfigurationRepositoryTest extends TestCase
{
    private ImmutableConfigurationRepository $configuration;

    protected function setUp(): void
    {
        $this->configuration = new ImmutableConfigurationRepository([
            'app' => [
                'name' => 'SIF',
                'debug' => true,
                'workers' => 4,
                'ratio' => 1.5,
                'nullable' => null,
                'modules' => ['runtime', 'container'],
            ],
        ]);
    }

    public function testRepositoryPreservesLegacyReadContract(): void
    {
        self::assertTrue($this->configuration->has('app.name'));
        self::assertSame('SIF', $this->configuration->get('app.name'));
        self::assertSame('fallback', $this->configuration->get('missing', 'fallback'));
        self::assertSame('SIF', $this->configuration->require('app.name'));
        self::assertTrue($this->configuration->isFrozen());
    }

    public function testLookupAcceptsValueObjectAndDistinguishesNull(): void
    {
        $result = $this->configuration->lookup(
            new ConfigurationKey('app.nullable'),
        );

        self::assertTrue($result->isFound());
        self::assertNull($result->value());
        self::assertTrue($this->configuration->lookup('app.missing')->isMissing());
    }

    public function testTypedReadsReturnExactTypes(): void
    {
        self::assertSame('SIF', $this->configuration->string('app.name'));
        self::assertTrue($this->configuration->boolean('app.debug'));
        self::assertSame(4, $this->configuration->integer('app.workers'));
        self::assertSame(1.5, $this->configuration->float('app.ratio'));
        self::assertNull($this->configuration->nullableString('app.nullable'));
        self::assertSame(['runtime', 'container'], $this->configuration->array('app.modules'));
    }

    public function testMissingTypedReadThrowsNotFoundException(): void
    {
        $this->expectException(ConfigurationNotFoundException::class);

        $this->configuration->string('app.missing');
    }

    public function testTypedReadDoesNotCoerceValues(): void
    {
        $this->expectException(ConfigurationTypeMismatchException::class);

        $this->configuration->integer('app.name');
    }
}
