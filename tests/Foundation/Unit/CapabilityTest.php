<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Exceptions\InvalidCapabilityException;
use Sif\Foundation\Framework;

final class CapabilityTest extends TestCase
{
    public function testApplicationHasDefaultCapabilitiesInDeterministicOrder(): void
    {
        $application = Framework::create();

        self::assertSame(['runtime', 'foundation', 'providers', 'lifecycle'], $application->capabilities());
        self::assertTrue($application->hasCapability('runtime'));
        self::assertFalse($application->hasCapability('events'));
    }

    public function testCapabilityIsTrimmedLowercasedAndDeduplicated(): void
    {
        $application = Framework::create();
        $application->addCapability('  Runtime.Events  ');
        $application->addCapability('runtime.events');

        self::assertTrue($application->hasCapability(' RUNTIME.EVENTS '));
        self::assertSame(['runtime', 'foundation', 'providers', 'lifecycle', 'runtime.events'], $application->capabilities());
        self::assertSame(1, array_count_values($application->capabilities())['runtime.events']);
    }

    /** @return iterable<string, array{string}> */
    public static function invalidCapabilities(): iterable
    {
        yield 'empty' => [''];
        yield 'whitespace' => ['   '];
        yield 'internal space' => ['runtime events'];
        yield 'leading empty segment' => ['.runtime'];
        yield 'trailing empty segment' => ['runtime.'];
        yield 'middle empty segment' => ['runtime..events'];
        yield 'invalid slash' => ['runtime/events'];
        yield 'non ASCII' => ['ejecución'];
    }

    #[DataProvider('invalidCapabilities')]
    public function testInvalidCapabilityIsRejected(string $capability): void
    {
        $this->expectException(InvalidCapabilityException::class);
        Framework::create()->addCapability($capability);
    }

    public function testCapabilitiesAreIndependentBetweenApplications(): void
    {
        $first = Framework::create();
        $second = Framework::create();
        $first->addCapability('runtime.events');

        self::assertTrue($first->hasCapability('runtime.events'));
        self::assertFalse($second->hasCapability('runtime.events'));
        self::assertSame(['runtime', 'foundation', 'providers', 'lifecycle'], $second->capabilities());
    }
}
