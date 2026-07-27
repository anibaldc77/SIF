<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Container;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Container\DefinitionServiceContainer;
use Sif\Foundation\Container\ScopeIdentifier;
use Sif\Foundation\Container\ServiceDefinition;
use Sif\Foundation\Container\ServiceDefinitionRegistry;
use Sif\Foundation\Container\ServiceIdentifier;
use Sif\Foundation\Container\ServiceLifetime;
use Sif\Foundation\Container\ServiceTag;
use Sif\Foundation\Tests\Fixtures\Container\CounterService;
use Sif\Foundation\Tests\Fixtures\Container\ExampleService;

final class TaggedServiceTest extends TestCase
{
    protected function setUp(): void
    {
        CounterService::reset();
    }

    public function testTagOrderingUsesPriorityThenRegistrationOrder(): void
    {
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::forInstance(
                new ServiceIdentifier('first'),
                new ExampleService('first'),
                [new ServiceTag('handler', 10)],
            ),
        );
        $registry->register(
            ServiceDefinition::forInstance(
                new ServiceIdentifier('second'),
                new ExampleService('second'),
                [new ServiceTag('handler', 20)],
            ),
        );
        $registry->register(
            ServiceDefinition::forInstance(
                new ServiceIdentifier('third'),
                new ExampleService('third'),
                [new ServiceTag('handler', 10)],
            ),
        );

        $tagged = $registry->tagged('handler');

        self::assertSame(
            ['second', 'first', 'third'],
            array_map(
                static fn ($entry): string => $entry->identifier()->value(),
                $tagged,
            ),
        );
    }

    public function testTagMetadataIsPreserved(): void
    {
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::forInstance(
                new ServiceIdentifier('serializer'),
                new ExampleService(),
                [
                    new ServiceTag(
                        name: 'serializer',
                        priority: 5,
                        metadata: ['format' => 'json'],
                    ),
                ],
            ),
        );

        $tagged = $registry->tagged('serializer');

        self::assertSame(
            ['format' => 'json'],
            $tagged[0]->tag()->metadata(),
        );
    }

    public function testRootContainerResolvesTaggedServicesInOrder(): void
    {
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::forInstance(
                new ServiceIdentifier('low'),
                new ExampleService('low'),
                [new ServiceTag('handler', 1)],
            ),
        );
        $registry->register(
            ServiceDefinition::forInstance(
                new ServiceIdentifier('high'),
                new ExampleService('high'),
                [new ServiceTag('handler', 50)],
            ),
        );

        $services = (new DefinitionServiceContainer($registry))
            ->resolveTagged('handler');

        $names = array_map(
            static function (object $service): string {
                if (!$service instanceof ExampleService) {
                    throw new \LogicException(
                        'Tagged service has an unexpected type.',
                    );
                }

                return $service->name;
            },
            $services,
        );

        self::assertSame(['high', 'low'], $names);
    }

    public function testScopeResolvesScopedTaggedServicesWithinSameScope(): void
    {
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::forClass(
                new ServiceIdentifier('scoped.handler'),
                CounterService::class,
                ServiceLifetime::Scoped,
                [new ServiceTag('handler')],
            ),
        );

        $scope = (new DefinitionServiceContainer($registry))
            ->beginScope(new ScopeIdentifier('tagged'));

        $first = $scope->resolveTagged('handler');
        $second = $scope->resolveTagged('handler');

        self::assertSame($first[0], $second[0]);
        self::assertSame(1, CounterService::constructed());
    }

    public function testUnknownTagReturnsEmptyLists(): void
    {
        $container = new DefinitionServiceContainer(
            new ServiceDefinitionRegistry(),
        );

        self::assertSame([], $container->tagged('missing'));
        self::assertSame([], $container->resolveTagged('missing'));
    }
}
