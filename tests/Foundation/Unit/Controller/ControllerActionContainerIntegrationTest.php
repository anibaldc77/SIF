<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Contracts\LazyServiceReferenceInterface;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\StringServiceContainerInterface;
use Sif\Foundation\Controller\Action\ControllerActionDefinition;
use Sif\Foundation\Controller\Action\ControllerActionDispatcher;
use Sif\Foundation\Controller\Action\ControllerActionHandlerRegistrar;
use Sif\Foundation\Controller\Action\ControllerActionRegistry;
use Sif\Foundation\Controller\Api\ApiResult;
use Sif\Foundation\Controller\Argument\ActionArgumentDefinition;
use Sif\Foundation\Controller\Argument\ActionArgumentResolver;
use Sif\Foundation\Controller\Argument\ActionArgumentSource;
use Sif\Foundation\Controller\Argument\ActionArgumentType;
use Sif\Foundation\Controller\Exceptions\ControllerActionException;
use Sif\Foundation\Controller\Resolver\ContainerActionServiceResolver;
use Sif\Foundation\Controller\Resolver\ContainerControllerResolver;
use Sif\Foundation\Http\Dispatch\HandlerRegistry;
use Sif\Foundation\Http\Value\HttpMethod;
use Sif\Foundation\Http\Value\Request;
use Sif\Foundation\Http\Value\Uri;

final class ControllerActionContainerIntegrationTest extends TestCase
{
    public function testRegisteredControllerActionResolvesArgumentsServicesAndApiResponse(): void
    {
        $container = new ControllerTestContainer([
            'app.controller.greeting' => new GreetingController(),
            'app.formatter' => new GreetingFormatter(),
        ]);
        $actions = new ControllerActionRegistry();
        $actions->register(new ControllerActionDefinition(
            'controller.greeting.show',
            'app.controller.greeting',
            'show',
            [
                new ActionArgumentDefinition('id', ActionArgumentSource::Route, ActionArgumentType::Integer),
                new ActionArgumentDefinition(
                    'formatter',
                    ActionArgumentSource::Service,
                    ActionArgumentType::Service,
                    'app.formatter',
                ),
            ],
        ));

        $dispatcher = new ControllerActionDispatcher(
            $actions,
            new ContainerControllerResolver($container),
            new ActionArgumentResolver(serviceResolver: new ContainerActionServiceResolver($container)),
        );
        $handlers = new HandlerRegistry();
        (new ControllerActionHandlerRegistrar($actions, $dispatcher))->registerInto($handlers);

        $request = (new Request(HttpMethod::Get, new Uri(path: '/greetings/42')))
            ->withAttribute('route.parameters', ['id' => '42'])
            ->withHeader('Accept', 'application/json');
        $response = $handlers->resolve('controller.greeting.show')->handle($request);

        self::assertSame(200, $response->status()->code());
        self::assertSame('{"message":"Hello #42"}', $response->body()->contents());
        self::assertSame(1, $handlers->count());
    }

    public function testRegistryRejectsDuplicateActionIdentifiers(): void
    {
        $action = new ControllerActionDefinition('controller.test', 'app.controller.test', 'show');
        $registry = new ControllerActionRegistry();
        $registry->register($action);

        $this->expectException(ControllerActionException::class);
        $registry->register($action);
    }

    public function testDispatcherRejectsUnsupportedControllerResult(): void
    {
        $container = new ControllerTestContainer([
            'app.controller.invalid' => new InvalidResultController(),
        ]);
        $actions = new ControllerActionRegistry();
        $actions->register(new ControllerActionDefinition(
            'controller.invalid.show',
            'app.controller.invalid',
            'show',
        ));
        $dispatcher = new ControllerActionDispatcher(
            $actions,
            new ContainerControllerResolver($container),
            new ActionArgumentResolver(),
        );

        $this->expectException(ControllerActionException::class);
        $dispatcher->dispatch(
            'controller.invalid.show',
            new Request(HttpMethod::Get, new Uri(path: '/invalid')),
        );
    }

    public function testDispatcherRejectsSignatureDriftFromRegisteredDefinition(): void
    {
        $container = new ControllerTestContainer([
            'app.controller.greeting' => new GreetingController(),
        ]);
        $actions = new ControllerActionRegistry();
        $actions->register(new ControllerActionDefinition(
            'controller.greeting.invalid',
            'app.controller.greeting',
            'show',
        ));
        $dispatcher = new ControllerActionDispatcher(
            $actions,
            new ContainerControllerResolver($container),
            new ActionArgumentResolver(),
        );

        $this->expectException(ControllerActionException::class);
        $dispatcher->dispatch(
            'controller.greeting.invalid',
            new Request(HttpMethod::Get, new Uri(path: '/greetings')),
        );
    }
}

final class GreetingController
{
    public function show(int $id, GreetingFormatter $formatter): ApiResult
    {
        return new ApiResult(['message' => $formatter->format($id)]);
    }
}

final class InvalidResultController
{
    public function show(): string
    {
        return 'unsupported';
    }
}

final class GreetingFormatter
{
    public function format(int $id): string
    {
        return sprintf('Hello #%d', $id);
    }
}

final class ControllerTestContainer implements StringServiceContainerInterface
{
    /** @param array<string, object> $services */
    public function __construct(private array $services)
    {
    }

    public function has(string $identifier): bool
    {
        return isset($this->services[$identifier]);
    }

    public function get(string $identifier): object
    {
        if (!isset($this->services[$identifier])) {
            throw new \RuntimeException(sprintf('Unknown test service "%s".', $identifier));
        }

        return $this->services[$identifier];
    }

    public function lazy(string $identifier): LazyServiceReferenceInterface
    {
        throw new \LogicException('Lazy services are not used by this test container.');
    }

    public function beginScope(string $identifier): StringServiceContainerInterface
    {
        return $this;
    }
}
