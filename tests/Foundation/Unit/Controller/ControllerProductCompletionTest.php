<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Contracts\RequestHandlerInterface;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\Controller\Action\ControllerActionDefinition;
use Sif\Foundation\Controller\Action\ControllerActionRegistry;
use Sif\Foundation\Controller\Api\ApiResponseFactory;
use Sif\Foundation\Controller\Api\ApiResult;
use Sif\Foundation\Controller\Problem\ProblemDetails;
use Sif\Foundation\Http\Dispatch\HandlerRegistry;
use Sif\Foundation\Http\Value\HttpMethod;
use Sif\Foundation\Http\Value\Request;
use Sif\Foundation\Http\Value\Response;
use Sif\Foundation\Http\Value\Uri;

final class ControllerProductCompletionTest extends TestCase
{
    public function testExistingHttpHandlersRemainValidWithoutControllerActions(): void
    {
        $handlers = new HandlerRegistry();
        $handlers->register('legacy.health', new ControllerCompletionLegacyHandler());

        $response = $handlers->resolve('legacy.health')->handle(
            new Request(HttpMethod::Get, new Uri(path: '/legacy-health')),
        );

        self::assertSame(200, $response->status()->code());
        self::assertSame('legacy-ok', $response->body()->contents());
    }

    public function testControllerActionsRequireExplicitRegistration(): void
    {
        $registry = new ControllerActionRegistry();

        self::assertSame(0, $registry->count());
        self::assertFalse($registry->has('controller.health.show'));

        $registry->register(new ControllerActionDefinition(
            'controller.health.show',
            'controller.health',
            'show',
        ));

        self::assertTrue($registry->has('controller.health.show'));
        self::assertSame('show', $registry->resolve('controller.health.show')->method());
    }

    public function testApiResultRemainsTransportNeutralUntilResponseFactoryIsUsed(): void
    {
        $result = new ApiResult([
            'data' => [
                'status' => 'ok',
            ],
        ]);

        self::assertSame(200, $result->status());
        self::assertSame(['data' => ['status' => 'ok']], $result->data());

        $request = (new Request(HttpMethod::Get, new Uri(path: '/health')))->withHeader('Accept', 'application/json');
        $response = (new ApiResponseFactory())->create($request, $result);

        self::assertSame(200, $response->status()->code());
        self::assertSame('application/json; charset=utf-8', $response->headers()->line('Content-Type'));
        self::assertSame('{"data":{"status":"ok"}}', $response->body()->contents());
    }

    public function testProblemDetailsExposeOnlyDeclaredStructuredFields(): void
    {
        $problem = new ProblemDetails(
            'https://sif.dev/problems/validation-failed',
            'Validation failed',
            422,
            'The submitted data is invalid.',
            '/widgets',
            [
                'errors' => [
                    ['code' => 'required', 'path' => 'body.name'],
                ],
            ],
        );

        self::assertSame([
            'type' => 'https://sif.dev/problems/validation-failed',
            'title' => 'Validation failed',
            'status' => 422,
            'detail' => 'The submitted data is invalid.',
            'instance' => '/widgets',
            'errors' => [
                ['code' => 'required', 'path' => 'body.name'],
            ],
        ], $problem->toArray());
    }
}

final class ControllerCompletionLegacyHandler implements RequestHandlerInterface
{
    public function handle(RequestInterface $request): ResponseInterface
    {
        return Response::text('legacy-ok');
    }
}
