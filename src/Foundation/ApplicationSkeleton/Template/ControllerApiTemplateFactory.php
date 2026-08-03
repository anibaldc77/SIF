<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Template;

use Sif\Foundation\ApplicationSkeleton\Exceptions\InvalidSkeletonValueException;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonArtifact;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonArtifactType;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectManifest;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectPathDefinition;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOverwritePolicy;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOwnership;

final readonly class ControllerApiTemplateFactory
{
    public function __construct(private ApplicationTemplateRenderer $renderer = new ApplicationTemplateRenderer())
    {
    }

    /** @return list<SkeletonArtifact> */
    public function artifacts(ProjectManifest $manifest): array
    {
        return [
            $this->directory($manifest, 'app/Controllers'),
            $this->healthController($manifest),
            $this->controllerServiceProvider($manifest),
            $this->apiRoutes($manifest),
            $this->controllerConfiguration($manifest),
            $this->featureTest($manifest),
        ];
    }

    public function healthController(ProjectManifest $manifest): SkeletonArtifact
    {
        $definition = $this->requireUserOwnedPath($manifest, 'app/Controllers/HealthController.php');
        $template = new ApplicationTemplate('health-controller', <<<'TPL'
<?php

declare(strict_types=1);

namespace {{project_namespace}}\Controllers;

use Sif\Foundation\Controller\Api\ApiResult;

final readonly class HealthController
{
    public function show(): ApiResult
    {
        return new ApiResult([
            'data' => [
                'status' => 'ok',
            ],
        ]);
    }
}
TPL
        );

        return $this->file($definition, $this->renderer->render($template, [
            'project_namespace' => $manifest->namespace()->value(),
        ]));
    }

    public function controllerServiceProvider(ProjectManifest $manifest): SkeletonArtifact
    {
        $definition = $this->requireUserOwnedPath($manifest, 'app/Providers/ControllerServiceProvider.php');
        $template = new ApplicationTemplate('controller-service-provider', <<<'TPL'
<?php

declare(strict_types=1);

namespace {{project_namespace}}\Providers;

use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Controller\Action\ControllerActionDefinition;
use Sif\Foundation\Controller\Action\ControllerActionHandlerRegistrar;
use Sif\Foundation\Controller\Action\ControllerActionRegistry;
use Sif\Foundation\Http\Dispatch\HandlerRegistry;
use Sif\Foundation\Http\Routing\RouteDefinition;
use Sif\Foundation\Http\Routing\RouteRegistry;
use Sif\Foundation\ServiceProvider;

final class ControllerServiceProvider extends ServiceProvider
{
    public function __construct(
        private readonly ControllerActionRegistry $actions,
        private readonly RouteRegistry $routes,
        private readonly HandlerRegistry $handlers,
        private readonly ControllerActionHandlerRegistrar $registrar,
    ) {
    }

    public function register(ApplicationInterface $application): void
    {
        $this->actions->register(new ControllerActionDefinition(
            'api.health',
            'controller.health',
            'show',
        ));

        /** @var list<RouteDefinition> $routes */
        $routes = require dirname(__DIR__, 2) . '/routes/api.php';
        foreach ($routes as $route) {
            $this->routes->register($route);
        }

        $this->registrar->registerInto($this->handlers);
    }
}
TPL
        );

        return $this->file($definition, $this->renderer->render($template, [
            'project_namespace' => $manifest->namespace()->value(),
        ]));
    }

    public function apiRoutes(ProjectManifest $manifest): SkeletonArtifact
    {
        $definition = $this->requireUserOwnedPath($manifest, 'routes/api.php');
        $template = new ApplicationTemplate('api-routes', <<<'TPL'
<?php

declare(strict_types=1);

use Sif\Foundation\Http\Routing\RouteDefinition;
use Sif\Foundation\Http\Routing\RouteName;
use Sif\Foundation\Http\Value\HttpMethod;

return [
    new RouteDefinition(
        new RouteName('api.health'),
        [HttpMethod::Get],
        '/api/health',
        'api.health',
    ),
];
TPL
        );

        return $this->file($definition, $this->renderer->render($template, []));
    }

    public function controllerConfiguration(ProjectManifest $manifest): SkeletonArtifact
    {
        $definition = $this->requireUserOwnedPath($manifest, 'config/controllers.php');
        $template = new ApplicationTemplate('controller-configuration', <<<'TPL'
<?php

declare(strict_types=1);

use {{project_namespace}}\Controllers\HealthController;

return [
    'controller.health' => HealthController::class,
];
TPL
        );

        return $this->file($definition, $this->renderer->render($template, [
            'project_namespace' => $manifest->namespace()->value(),
        ]));
    }

    public function featureTest(ProjectManifest $manifest): SkeletonArtifact
    {
        $definition = $this->requireUserOwnedPath($manifest, 'tests/Feature/HealthApiTest.php');
        $template = new ApplicationTemplate('health-api-feature-test', <<<'TPL'
<?php

declare(strict_types=1);

namespace {{project_namespace}}\Tests\Feature;

use PHPUnit\Framework\TestCase;
use {{project_namespace}}\Controllers\HealthController;

final class HealthApiTest extends TestCase
{
    public function testHealthActionReturnsReadyPayload(): void
    {
        $result = (new HealthController())->show();

        self::assertSame(200, $result->status());
        self::assertSame(['data' => ['status' => 'ok']], $result->data());
    }
}
TPL
        );

        return $this->file($definition, $this->renderer->render($template, [
            'project_namespace' => $manifest->namespace()->value(),
        ]));
    }

    private function directory(ProjectManifest $manifest, string $path): SkeletonArtifact
    {
        return new SkeletonArtifact($this->requireUserOwnedPath($manifest, $path), SkeletonArtifactType::Directory);
    }

    private function file(ProjectPathDefinition $definition, string $content): SkeletonArtifact
    {
        return new SkeletonArtifact($definition, SkeletonArtifactType::File, $content . "\n");
    }

    private function requireUserOwnedPath(ProjectManifest $manifest, string $path): ProjectPathDefinition
    {
        $definition = $manifest->paths()[$path] ?? null;
        if (!$definition instanceof ProjectPathDefinition) {
            throw new InvalidSkeletonValueException(sprintf(
                'Controller API path "%s" is not declared by the project manifest.',
                $path,
            ));
        }
        if ($definition->ownership() !== SkeletonOwnership::UserOwned) {
            throw new InvalidSkeletonValueException(sprintf('Controller API path "%s" must be user-owned.', $path));
        }
        if ($definition->overwritePolicy() !== SkeletonOverwritePolicy::Fail) {
            throw new InvalidSkeletonValueException(sprintf(
                'Controller API path "%s" must use the fail overwrite policy.',
                $path,
            ));
        }

        return $definition;
    }
}
