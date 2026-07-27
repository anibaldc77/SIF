<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Integration\Container;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Container\ConstructorArgumentBinding;
use Sif\Foundation\Container\ConstructorArgumentBindings;
use Sif\Foundation\Container\ContainerCompositionFactory;
use Sif\Foundation\Container\ContextualBinding;
use Sif\Foundation\Container\ScopeIdentifier;
use Sif\Foundation\Container\ServiceDefinition;
use Sif\Foundation\Container\ServiceIdentifier;
use Sif\Foundation\Container\ServiceLifetime;
use Sif\Foundation\Container\ServiceTag;
use Sif\Foundation\Tests\Fixtures\Container\ContextualConsumer;
use Sif\Foundation\Tests\Fixtures\Container\CounterService;
use Sif\Foundation\Tests\Fixtures\Container\ExampleService;

final class ContainerReferenceIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        CounterService::reset();
    }

    public function testCompleteReferenceFlow(): void
    {
        $composition = (new ContainerCompositionFactory())->create();
        $definitions = $composition->definitions();

        $definitions->register(
            ServiceDefinition::forInstance(
                new ServiceIdentifier('logger.default'),
                new ExampleService('default'),
            ),
        );
        $definitions->register(
            ServiceDefinition::forInstance(
                new ServiceIdentifier('logger.audit'),
                new ExampleService('audit'),
                [
                    new ServiceTag(
                        name: 'logger',
                        priority: 100,
                        metadata: ['channel' => 'audit'],
                    ),
                ],
            ),
        );
        $definitions->register(
            ServiceDefinition::forClass(
                new ServiceIdentifier(CounterService::class),
                CounterService::class,
                ServiceLifetime::Scoped,
                [new ServiceTag('request.service')],
            ),
        );
        $definitions->register(
            ServiceDefinition::forAutowiredClass(
                new ServiceIdentifier(ContextualConsumer::class),
                ContextualConsumer::class,
                ServiceLifetime::Scoped,
            ),
        );

        $composition->contextualBindings()->register(
            new ContextualBinding(
                consumer: new ServiceIdentifier(
                    ContextualConsumer::class,
                ),
                parameterName: 'dependency',
                binding: ConstructorArgumentBinding::service(
                    new ServiceIdentifier('logger.audit'),
                ),
            ),
        );

        $report = $composition->validator()->validate();

        self::assertTrue($report->isValid());

        $compiled = $composition->compiler()->compile();

        self::assertNotSame('', $compiled->fingerprint());

        $scope = $composition->container()->beginScope(
            new ScopeIdentifier('reference'),
        );

        $consumer = $scope->get(
            new ServiceIdentifier(ContextualConsumer::class),
        );

        self::assertInstanceOf(ContextualConsumer::class, $consumer);
        self::assertSame('audit', $consumer->dependency->name);
        self::assertCount(1, $scope->resolveTagged('request.service'));

        $lazy = $scope->lazy(
            new ServiceIdentifier(CounterService::class),
        );

        self::assertFalse($lazy->isResolved());
        self::assertSame(
            $scope->get(new ServiceIdentifier(CounterService::class)),
            $lazy->resolve(),
        );

        $compatibleLogger = $composition
            ->compatibility()
            ->get('logger.audit');

        self::assertInstanceOf(
            ExampleService::class,
            $compatibleLogger,
        );
        self::assertSame('audit', $compatibleLogger->name);
    }
}
