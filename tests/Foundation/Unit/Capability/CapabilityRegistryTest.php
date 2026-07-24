<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Capability;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Capability\CapabilityRegistry;
use Sif\Foundation\Capability\Contracts\CapabilityInterface;
use Sif\Foundation\Capability\Exceptions\CapabilityNotFoundException;
use Sif\Foundation\Capability\Exceptions\DuplicateCapabilityException;
use Sif\Foundation\Capability\Exceptions\InvalidCapabilityIdentifierException;

final class CapabilityRegistryTest extends TestCase
{
    public function testItRegistersAndResolvesACapabilityByIdentifier(): void
    {
        $registry = new CapabilityRegistry();
        $capability = new TestCapability('cache');

        $registry->register($capability);

        self::assertTrue($registry->has('cache'));
        self::assertSame($capability, $registry->get('cache'));
        self::assertSame(1, $registry->count());
    }

    public function testItPreservesRegistrationOrder(): void
    {
        $registry = new CapabilityRegistry();
        $first = new TestCapability('first');
        $second = new TestCapability('second');

        $registry->register($first);
        $registry->register($second);

        self::assertSame([$first, $second], $registry->all());
        self::assertSame(
            ['first', 'second'],
            array_keys(iterator_to_array($registry)),
        );
    }

    public function testItTrimsIdentifiersAtTheRegistryBoundary(): void
    {
        $registry = new CapabilityRegistry();
        $capability = new TestCapability('  logging  ');

        $registry->register($capability);

        self::assertTrue($registry->has(' logging '));
        self::assertSame($capability, $registry->get('logging'));
    }

    public function testItRejectsDuplicateIdentifiers(): void
    {
        $registry = new CapabilityRegistry();
        $registry->register(new TestCapability('events'));

        $this->expectException(DuplicateCapabilityException::class);
        $this->expectExceptionMessage('Capability "events" is already registered.');

        $registry->register(new TestCapability(' events '));
    }

    public function testItRejectsAnEmptyCapabilityIdentifier(): void
    {
        $registry = new CapabilityRegistry();

        $this->expectException(InvalidCapabilityIdentifierException::class);
        $this->expectExceptionMessage('A capability identifier cannot be empty.');

        $registry->register(new TestCapability('   '));
    }

    public function testItRejectsAnEmptyLookupIdentifier(): void
    {
        $registry = new CapabilityRegistry();

        $this->expectException(InvalidCapabilityIdentifierException::class);

        $registry->has('');
    }

    public function testItReportsMissingCapabilities(): void
    {
        $registry = new CapabilityRegistry();

        $this->expectException(CapabilityNotFoundException::class);
        $this->expectExceptionMessage('Capability "queue" is not registered.');

        $registry->get('queue');
    }

    public function testItFindsCapabilitiesByRuntimeType(): void
    {
        $registry = new CapabilityRegistry();
        $cache = new TypedTestCapability('cache');
        $events = new TestCapability('events');

        $registry->register($cache);
        $registry->register($events);

        self::assertSame(
            [$cache],
            $registry->ofType(TypedCapability::class),
        );
    }

    public function testIdentifiersRemainCaseSensitive(): void
    {
        $registry = new CapabilityRegistry();
        $lower = new TestCapability('cache');
        $upper = new TestCapability('Cache');

        $registry->register($lower);
        $registry->register($upper);

        self::assertSame($lower, $registry->get('cache'));
        self::assertSame($upper, $registry->get('Cache'));
        self::assertSame(2, $registry->count());
    }
}

interface TypedCapability
{
}

class TestCapability implements CapabilityInterface
{
    public function __construct(private readonly string $identifier)
    {
    }

    public function identifier(): string
    {
        return $this->identifier;
    }
}

final class TypedTestCapability extends TestCapability implements TypedCapability
{
}
