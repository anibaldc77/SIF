<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Http;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\ApplicationSkeleton\Bootstrap\ApplicationTemplateBlueprintFactory;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonArtifactType;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectEntryPoint;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectManifest;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectPathDefinition;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectIdentifier;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectNamespace;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectPath;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOverwritePolicy;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOwnership;
use Sif\Foundation\Bootstrap;
use Sif\Foundation\Context\ContextAttributes;
use Sif\Foundation\Context\ContextId;
use Sif\Foundation\Context\ExecutionContext;
use Sif\Foundation\Contracts\ExecutionContextFactoryInterface;
use Sif\Foundation\Contracts\ExecutionContextInterface;
use Sif\Foundation\Contracts\RequestHandlerInterface;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\Environment;
use Sif\Foundation\ErrorHandling\Contracts\ErrorHandlerInterface;
use Sif\Foundation\ErrorHandling\FailureOrigin;
use Sif\Foundation\ErrorHandling\Orchestration\ErrorHandlingResult;
use Sif\Foundation\Http\Dispatch\HandlerDispatcher;
use Sif\Foundation\Http\Dispatch\HandlerRegistry;
use Sif\Foundation\Http\Lifecycle\HttpRequestLifecycleCoordinator;
use Sif\Foundation\Http\Middleware\MiddlewareRegistry;
use Sif\Foundation\Http\Routing\RouteDefinition;
use Sif\Foundation\Http\Routing\RouteMatcher;
use Sif\Foundation\Http\Routing\RouteName;
use Sif\Foundation\Http\Routing\RouteRegistry;
use Sif\Foundation\Http\Runtime\HttpRuntime;
use Sif\Foundation\Http\Runtime\HttpRuntimeServiceProvider;
use Sif\Foundation\Http\Runtime\NativeHttpKernel;
use Sif\Foundation\Http\Value\HttpMethod;
use Sif\Foundation\Http\Value\Request;
use Sif\Foundation\Http\Value\Response;
use Sif\Foundation\Http\Value\Uri;

final class HttpRuntimeSkeletonIntegrationTest extends TestCase
{
    public function testRuntimeHandlesRequestAndExposesSafeSummary(): void
    {
        $runtime = self::runtime();

        self::assertSame([
            'native_kernel' => true,
            'request_lifecycle' => true,
            'response_emission' => true,
        ], $runtime->summary());
        self::assertSame(200, $runtime->handle(new Request(HttpMethod::Get, new Uri(path: '/health')))->status()->code());
    }

    public function testProviderAndBootstrapPublishOptionalHttpRuntime(): void
    {
        $runtime = self::runtime();
        $provider = new HttpRuntimeServiceProvider($runtime);
        $capabilities = [];
        foreach ($provider->capabilities() as $capability) {
            $capabilities[] = $capability->identifier();
        }

        self::assertSame(['http', 'http.lifecycle', 'http.native-transport'], $capabilities);

        $application = (new Bootstrap(http: $runtime))->createApplication(Environment::testing());
        self::assertSame($runtime, $application->http());
    }

    public function testSkeletonPublicEntryPointDelegatesToHttpRuntime(): void
    {
        $blueprint = (new ApplicationTemplateBlueprintFactory())->create(self::manifest());
        $publicIndex = null;
        foreach ($blueprint->artifacts() as $artifact) {
            if ($artifact->type() === SkeletonArtifactType::File
                && $artifact->path()->path()->value() === 'public/index.php') {
                $publicIndex = $artifact->content();
            }
        }

        self::assertNotNull($publicIndex);
        self::assertStringContainsString('$application->http()', $publicIndex);
        self::assertStringContainsString('runNative(new NativeResponseEmitter())', $publicIndex);
    }

    private static function runtime(): HttpRuntime
    {
        $routes = new RouteRegistry();
        $routes->register(new RouteDefinition(new RouteName('health'), [HttpMethod::Get], '/health', 'health'));
        $handlers = new HandlerRegistry();
        $handlers->register('health', new RuntimeHealthHandler());

        return new HttpRuntime(new NativeHttpKernel(new HttpRequestLifecycleCoordinator(
            new RouteMatcher($routes),
            new HandlerDispatcher($handlers, new MiddlewareRegistry()),
            new RuntimeContextFactory(),
            new RuntimeUnusedErrorHandler(),
        )));
    }

    private static function manifest(): ProjectManifest
    {
        $directories = [
            'app', 'app/Commands', 'app/Models', 'app/Modules', 'app/Providers',
            'bootstrap', 'config', 'database/migrations', 'public', 'resources',
            'routes', 'storage/cache', 'storage/logs', 'storage/runtime', 'tests',
        ];
        $files = [
            'bootstrap/app.php', 'bootstrap/cli.php', 'public/index.php',
            'config/app.php', 'config/database.php', '.env.example', '.gitignore',
            'composer.json', 'phpunit.xml', 'sif', 'sif.bat', 'sif.project.json',
        ];
        $paths = [];
        foreach ($directories as $path) {
            $paths[] = new ProjectPathDefinition(
                new ProjectPath($path),
                str_starts_with($path, 'storage/') ? SkeletonOwnership::RuntimeOwned : SkeletonOwnership::UserOwned,
                SkeletonOverwritePolicy::Skip,
            );
        }
        foreach ($files as $path) {
            $paths[] = new ProjectPathDefinition(
                new ProjectPath($path),
                SkeletonOwnership::SkeletonOwned,
                SkeletonOverwritePolicy::Replace,
            );
        }

        return new ProjectManifest(
            new ProjectIdentifier('http-app'),
            'HTTP Application',
            new ProjectNamespace('Example\\HttpApplication'),
            '1.0.0',
            '1.0.0',
            '^2.0',
            '8.2.0',
            [new ProjectEntryPoint('http', new ProjectPath('public/index.php'))],
            ['production'],
            $paths,
            ['http'],
        );
    }
}

final class RuntimeHealthHandler implements RequestHandlerInterface
{
    public function handle(RequestInterface $request): ResponseInterface
    {
        return Response::text('ok');
    }
}

final class RuntimeContextFactory implements ExecutionContextFactoryInterface
{
    public function createRoot(
        ContextAttributes $attributes = new ContextAttributes(),
        ?string $actorId = null,
        ?string $tenantId = null,
        ?string $operation = null,
        ?string $source = null,
        ?string $locale = null,
        ?string $timezone = null,
    ): ExecutionContext {
        $id = new ContextId('http-runtime');

        return new ExecutionContext(
            contextId: $id,
            correlationId: $id,
            createdAt: new DateTimeImmutable('2026-08-02T00:00:00+00:00'),
            attributes: $attributes,
            actorId: $actorId,
            tenantId: $tenantId,
            operation: $operation,
            source: $source,
            locale: $locale,
            timezone: $timezone,
        );
    }

    public function derive(
        ExecutionContextInterface $parent,
        ContextAttributes $attributes = new ContextAttributes(),
        ?ContextId $causationId = null,
        ?string $operation = null,
        ?string $source = null,
    ): ExecutionContext {
        return $this->createRoot($parent->attributes()->merged($attributes), operation: $operation, source: $source);
    }
}

final class RuntimeUnusedErrorHandler implements ErrorHandlerInterface
{
    public function handle(\Throwable $throwable, FailureOrigin $origin, array $metadata = [], int $attempt = 1): ErrorHandlingResult
    {
        throw new RuntimeException('Unexpected error handler invocation.');
    }
}
