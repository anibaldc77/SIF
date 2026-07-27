<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Container;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Container\ServiceDefinition;
use Sif\Foundation\Container\ServiceDefinitionRegistry;
use Sif\Foundation\Container\ServiceIdentifier;
use Sif\Foundation\Exceptions\DuplicateServiceDefinitionException;
use Sif\Foundation\Exceptions\ServiceDefinitionNotFoundException;
use Sif\Foundation\Tests\Fixtures\Container\ExampleService;

final class ServiceDefinitionRegistryTest extends TestCase
{
    public function testRegistryPreservesRegistrationOrder(): void
    {
        $first = ServiceDefinition::forClass(
            new ServiceIdentifier('first'),
            ExampleService::class,
        );
        $second = ServiceDefinition::forClass(
            new ServiceIdentifier('second'),
            ExampleService::class,
        );

        $registry = new ServiceDefinitionRegistry();
        $registry->register($first);
        $registry->register($second);

        self::assertSame([$first, $second], $registry->all());
    }

    public function testRegistryResolvesDefinitionByIdentifier(): void
    {
        $identifier = new ServiceIdentifier('example');
        $definition = ServiceDefinition::forClass(
            $identifier,
            ExampleService::class,
        );

        $registry = new ServiceDefinitionRegistry();
        $registry->register($definition);

        self::assertTrue($registry->has($identifier));
        self::assertSame($definition, $registry->get($identifier));
    }

    public function testRegistryRejectsDuplicateIdentifier(): void
    {
        $identifier = new ServiceIdentifier('example');
        $registry = new ServiceDefinitionRegistry();

        $registry->register(
            ServiceDefinition::forClass(
                $identifier,
                ExampleService::class,
            ),
        );

        $this->expectException(
            DuplicateServiceDefinitionException::class,
        );

        $registry->register(
            ServiceDefinition::forInstance(
                $identifier,
                new ExampleService(),
            ),
        );
    }

    public function testRegistryRejectsUnknownIdentifier(): void
    {
        $this->expectException(
            ServiceDefinitionNotFoundException::class,
        );

        (new ServiceDefinitionRegistry())->get(
            new ServiceIdentifier('missing'),
        );
    }

    public function testAliasIsStoredAsDefinitionWithoutResolution(): void
    {
        $alias = ServiceDefinition::alias(
            new ServiceIdentifier('contract'),
            new ServiceIdentifier('implementation'),
        );

        $registry = new ServiceDefinitionRegistry();
        $registry->register($alias);

        self::assertSame(
            'implementation',
            $registry
                ->get(new ServiceIdentifier('contract'))
                ->aliasTarget()
                ?->value(),
        );
    }
}
