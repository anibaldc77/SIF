<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Resources;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Resources\Exceptions\DuplicateResourceException;
use Sif\Foundation\Resources\Exceptions\InvalidRegistrationOrderException;
use Sif\Foundation\Resources\Exceptions\ResourceNotFoundException;
use Sif\Foundation\Resources\Registry\CompiledResourceRegistry;
use Sif\Foundation\Resources\Registry\RegisteredResource;
use Sif\Foundation\Resources\Registry\ResourceRegistry;
use Sif\Foundation\Resources\ResourceDescriptor;
use Sif\Foundation\Resources\ResourceIdentifier;
use Sif\Foundation\Resources\ResourceNamespace;
use Sif\Foundation\Resources\ResourcePath;
use Sif\Foundation\Resources\ResourcePriority;
use Sif\Foundation\Resources\ResourceType;

final class ResourceRegistryTest extends TestCase
{
    public function testRegistrationOrderIsMonotonicAndPreserved(): void
    {
        $registry = new ResourceRegistry();
        $first = $registry->register($this->descriptor('first'));
        $second = $registry->register($this->descriptor('second'));

        self::assertSame(0, $first->registrationOrder());
        self::assertSame(1, $second->registrationOrder());
    }

    public function testExactQualifiedDuplicateIsRejected(): void
    {
        $registry = new ResourceRegistry();
        $registry->register($this->descriptor('main', 'application'));

        $this->expectException(DuplicateResourceException::class);
        $registry->register($this->descriptor('main', 'application', 100));
    }

    public function testSameIdentifierInDifferentNamespacesIsAllowed(): void
    {
        $registry = new ResourceRegistry();
        $registry->register($this->descriptor('main', 'application'));
        $registry->register($this->descriptor('main', 'module'));

        self::assertSame(2, $registry->count());
    }

    public function testLookupIsExactAndCaseSensitive(): void
    {
        $registry = new ResourceRegistry();
        $descriptor = $this->descriptor('Main', 'Application');
        $registry->register($descriptor);

        self::assertTrue($registry->has(new ResourceNamespace('Application'), new ResourceIdentifier('Main')));
        self::assertSame($descriptor, $registry->get(new ResourceNamespace('Application'), new ResourceIdentifier('Main')));
        self::assertFalse($registry->has(new ResourceNamespace('application'), new ResourceIdentifier('Main')));
    }

    public function testMissingLookupRaisesTypedFailure(): void
    {
        $this->expectException(ResourceNotFoundException::class);
        (new ResourceRegistry())->get(ResourceNamespace::global(), new ResourceIdentifier('missing'));
    }

    public function testEntriesAreOrderedByPriorityThenRegistrationOrder(): void
    {
        $registry = new ResourceRegistry();
        $registry->register($this->descriptor('normal-first', priority: 0));
        $registry->register($this->descriptor('high-first', priority: 100));
        $registry->register($this->descriptor('high-second', priority: 100));
        $registry->register($this->descriptor('low', priority: -10));

        self::assertSame(
            ['high-first', 'high-second', 'normal-first', 'low'],
            array_map(
                static fn (RegisteredResource $entry): string => $entry->descriptor()->identifier()->value(),
                $registry->entries(),
            ),
        );
    }

    public function testCompileCreatesStableImmutableSnapshot(): void
    {
        $registry = new ResourceRegistry();
        $registry->register($this->descriptor('first'));
        $compiled = $registry->compile();
        $registry->register($this->descriptor('second'));

        self::assertInstanceOf(CompiledResourceRegistry::class, $compiled);
        self::assertSame(1, $compiled->count());
        self::assertSame(2, $registry->count());
        self::assertFalse($compiled->has(ResourceNamespace::global(), new ResourceIdentifier('second')));
    }

    public function testCompiledRegistryRejectsDuplicateKeys(): void
    {
        $descriptor = $this->descriptor('duplicate');

        $this->expectException(DuplicateResourceException::class);
        new CompiledResourceRegistry([
            new RegisteredResource($descriptor, 0),
            new RegisteredResource($descriptor, 1),
        ]);
    }

    public function testRegisteredResourceRejectsNegativeOrder(): void
    {
        $this->expectException(InvalidRegistrationOrderException::class);
        new RegisteredResource($this->descriptor('invalid'), -1);
    }

    public function testRegisteredResourceSummaryIsStable(): void
    {
        $entry = new RegisteredResource($this->descriptor('summary'), 4);

        self::assertSame(4, $entry->summary()['registration_order']);
        self::assertSame('summary', $entry->summary()['resource']['identifier']);
    }

    private function descriptor(string $identifier, string $namespace = 'global', int $priority = 0): ResourceDescriptor
    {
        return new ResourceDescriptor(
            new ResourceIdentifier($identifier),
            new ResourceNamespace($namespace),
            ResourceType::generic(),
            new ResourcePath('resources/' . strtolower($identifier) . '.json'),
            new ResourcePriority($priority),
        );
    }
}
