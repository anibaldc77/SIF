<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Lifecycle;

use Sif\Foundation\Context\ContextAttributes;
use Sif\Foundation\Contracts\EventDispatcherInterface;
use Sif\Foundation\Contracts\ExecutionContextFactoryInterface;
use Sif\Foundation\Contracts\ExecutionContextInterface;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\ErrorHandling\Contracts\ErrorHandlerInterface;
use Sif\Foundation\ErrorHandling\FailureOrigin;
use Sif\Foundation\Http\Dispatch\HandlerDispatcher;
use Sif\Foundation\Http\Error\HttpErrorResponseFactory;
use Sif\Foundation\Http\Event\HttpRequestCompleted;
use Sif\Foundation\Http\Event\HttpRequestFailed;
use Sif\Foundation\Http\Event\HttpRequestStarted;
use Sif\Foundation\Http\Routing\RouteMatchStatus;
use Sif\Foundation\Http\Routing\RouteMatcher;
use Sif\Foundation\Logging\Contracts\LoggerInterface;
use Throwable;

final class HttpRequestLifecycleCoordinator
{
    public function __construct(
        private RouteMatcher $matcher,
        private HandlerDispatcher $dispatcher,
        private ExecutionContextFactoryInterface $contextFactory,
        private ErrorHandlerInterface $errorHandler,
        private HttpErrorResponseFactory $errorResponses = new HttpErrorResponseFactory(),
        private ?EventDispatcherInterface $events = null,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function handle(RequestInterface $request): ResponseInterface
    {
        $context = $this->createContext($request);
        $request = $request->withAttribute('execution.context', $context);

        $this->dispatchEvent(new HttpRequestStarted($request, $context));
        $this->logStarted($request, $context);

        try {
            $match = $this->matcher->match($request->method(), $request->uri()->path());

            if ($match->status() === RouteMatchStatus::NotFound) {
                $response = $this->errorResponses->notFound();
            } elseif ($match->status() === RouteMatchStatus::MethodNotAllowed) {
                $response = $this->errorResponses->methodNotAllowed(array_map(static fn ($method): string => $method->value, $match->allowedMethods()));
            } else {
                $response = $this->dispatcher->dispatch($request, $match);
            }

            $this->dispatchEvent(new HttpRequestCompleted($request, $response, $context));
            $this->logCompleted($request, $response, $context);

            return $response;
        } catch (Throwable $throwable) {
            $result = $this->errorHandler->handle(
                $throwable,
                new FailureOrigin('http.request'),
                $this->safeMetadata($request, $context),
            );
            $response = $this->errorResponses->internalFailure($result);

            $this->dispatchEvent(new HttpRequestFailed($request, $throwable, $response, $context));
            $this->logFailed($request, $response, $context, $throwable);

            return $response;
        }
    }

    private function createContext(RequestInterface $request): ExecutionContextInterface
    {
        return $this->contextFactory->createRoot(
            new ContextAttributes([
                'http.method' => $request->method()->value,
                'http.path' => $request->uri()->path(),
            ]),
            operation: 'http.request',
            source: 'http',
        );
    }

    private function dispatchEvent(object $event): void
    {
        $this->events?->dispatch($event);
    }

    private function logStarted(RequestInterface $request, ExecutionContextInterface $context): void
    {
        $this->logger?->info('HTTP request started.', $this->safeMetadata($request, $context));
    }

    private function logCompleted(
        RequestInterface $request,
        ResponseInterface $response,
        ExecutionContextInterface $context,
    ): void {
        $metadata = $this->safeMetadata($request, $context);
        $metadata['http.status'] = $response->status()->code();
        $this->logger?->info('HTTP request completed.', $metadata);
    }

    private function logFailed(
        RequestInterface $request,
        ResponseInterface $response,
        ExecutionContextInterface $context,
        Throwable $throwable,
    ): void {
        $metadata = $this->safeMetadata($request, $context);
        $metadata['http.status'] = $response->status()->code();
        $this->logger?->error('HTTP request failed.', $metadata, $throwable);
    }

    /** @return array<string, null|bool|int|float|string|array<mixed>> */
    private function safeMetadata(RequestInterface $request, ExecutionContextInterface $context): array
    {
        return [
            'context_id' => $context->contextId()->value(),
            'correlation_id' => $context->correlationId()->value(),
            'http.method' => $request->method()->value,
            'http.path' => $request->uri()->path(),
        ];
    }
}
