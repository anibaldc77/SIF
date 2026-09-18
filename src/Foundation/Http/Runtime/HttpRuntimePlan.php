<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Runtime;

use Sif\Foundation\Context\ExecutionContextFactory;
use Sif\Foundation\Context\RandomContextIdGenerator;
use Sif\Foundation\Context\SystemClock;
use Sif\Foundation\Contracts\EventDispatcherInterface;
use Sif\Foundation\Contracts\ExecutionContextFactoryInterface;
use Sif\Foundation\Contracts\HttpHandlerResolverInterface;
use Sif\Foundation\Contracts\HttpMiddlewareResolverInterface;
use Sif\Foundation\ErrorHandling\Classification\OrderedThrowableClassifier;
use Sif\Foundation\ErrorHandling\Clock\SystemFailureClock;
use Sif\Foundation\ErrorHandling\Factory\FailureEnvelopeFactory;
use Sif\Foundation\ErrorHandling\Factory\RandomFailureIdGenerator;
use Sif\Foundation\ErrorHandling\Metadata\SafeFailureMetadataNormalizer;
use Sif\Foundation\ErrorHandling\Planning\ErrorHandlingPlan;
use Sif\Foundation\ErrorHandling\Contracts\ErrorHandlerInterface;
use Sif\Foundation\ErrorHandling\Recovery\OrderedRecoveryDecider;
use Sif\Foundation\ErrorHandling\Reporting\AcceptAllFailureReportFilter;
use Sif\Foundation\ErrorHandling\Reporting\FailureReportRoute;
use Sif\Foundation\ErrorHandling\Reporting\FailureReporterDispatcher;
use Sif\Foundation\ErrorHandling\Reporting\NativeFailureReporter;
use Sif\Foundation\ErrorHandling\Reporting\NullEmergencyFailureReporter;
use Sif\Foundation\Http\Dispatch\HandlerDispatcher;
use Sif\Foundation\Http\Dispatch\HandlerRegistry;
use Sif\Foundation\Http\Lifecycle\HttpRequestLifecycleCoordinator;
use Sif\Foundation\Http\Middleware\MiddlewareRegistry;
use Sif\Foundation\Http\Routing\RouteMatcher;
use Sif\Foundation\Http\Routing\RouteRegistry;
use Sif\Foundation\Logging\Contracts\LoggerInterface;

/** Foundation-owned composition; applications supply only their HTTP registrations. */
final readonly class HttpRuntimePlan
{
    /** @param list<string> $globalMiddleware */
    public function __construct(
        private RouteRegistry $routes = new RouteRegistry(),
        private HttpHandlerResolverInterface $handlers = new HandlerRegistry(),
        private HttpMiddlewareResolverInterface $middleware = new MiddlewareRegistry(),
        private array $globalMiddleware = [],
        private ?ExecutionContextFactoryInterface $contextFactory = null,
        private ?EventDispatcherInterface $events = null,
    ) {
    }

    public function create(ErrorHandlerInterface $errorHandler, ?LoggerInterface $logger = null): HttpRuntime
    {
        return new HttpRuntime(new NativeHttpKernel(new HttpRequestLifecycleCoordinator(
            new RouteMatcher($this->routes),
            new HandlerDispatcher($this->handlers, $this->middleware, $this->globalMiddleware),
            $this->contextFactory ?? new ExecutionContextFactory(new RandomContextIdGenerator(), new SystemClock()),
            $errorHandler,
            events: $this->events,
            logger: $logger,
        )));
    }

    public function defaultErrorHandlingPlan(): ErrorHandlingPlan
    {
        return new ErrorHandlingPlan(
            OrderedThrowableClassifier::withUnknownFallback([]),
            new FailureEnvelopeFactory(new RandomFailureIdGenerator(), new SystemFailureClock(), new SafeFailureMetadataNormalizer()),
            OrderedRecoveryDecider::withRethrowFallback([]),
            new FailureReporterDispatcher([
                new FailureReportRoute('http.native', new AcceptAllFailureReportFilter(), new NativeFailureReporter()),
            ], new NullEmergencyFailureReporter()),
        );
    }
}
