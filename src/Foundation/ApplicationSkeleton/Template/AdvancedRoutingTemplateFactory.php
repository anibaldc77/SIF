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

final readonly class AdvancedRoutingTemplateFactory
{
    /** @return list<SkeletonArtifact> */
    public function artifacts(ProjectManifest $manifest): array
    {
        return [
            $this->file($this->requireUserOwnedPath($manifest, 'config/routing.php'), <<<'PHPFILE'
<?php

declare(strict_types=1);

return [
    'cache' => [
        'enabled' => false,
        'path' => 'storage/cache/routes.json',
    ],
    'base_uri' => null,
];
PHPFILE
            ),
            $this->file($this->requireUserOwnedPath($manifest, 'routes/advanced.php'), <<<'PHPFILE'
<?php

declare(strict_types=1);

use Sif\Foundation\Http\Routing\Advanced\RouteDefaults;
use Sif\Foundation\Http\Routing\Advanced\RouteGroup;
use Sif\Foundation\Http\Routing\Advanced\RouteGroupDefinition;
use Sif\Foundation\Http\Routing\Advanced\RouteMetadata;
use Sif\Foundation\Http\Routing\RouteDefinition;
use Sif\Foundation\Http\Routing\RouteName;
use Sif\Foundation\Http\Value\HttpMethod;

return new RouteGroup(
    new RouteGroupDefinition(
        pathPrefix: '/api',
        namePrefix: 'api.',
        middleware: ['request-id'],
        metadata: new RouteMetadata(['surface' => 'api']),
        defaults: new RouteDefaults(['locale' => 'es']),
    ),
    routes: [
        new RouteDefinition(
            new RouteName('health'),
            [HttpMethod::Get],
            '/health',
            'api.health',
        ),
    ],
);
PHPFILE
            ),
        ];
    }

    private function file(ProjectPathDefinition $definition, string $content): SkeletonArtifact
    {
        return new SkeletonArtifact($definition, SkeletonArtifactType::File, $content . "\n");
    }

    private function requireUserOwnedPath(ProjectManifest $manifest, string $path): ProjectPathDefinition
    {
        $definition = $manifest->paths()[$path] ?? null;
        if (!$definition instanceof ProjectPathDefinition) {
            throw new InvalidSkeletonValueException(sprintf('Advanced routing path "%s" is not declared.', $path));
        }
        if ($definition->ownership() !== SkeletonOwnership::UserOwned) {
            throw new InvalidSkeletonValueException(sprintf('Advanced routing path "%s" must be user-owned.', $path));
        }
        if ($definition->overwritePolicy() !== SkeletonOverwritePolicy::Fail) {
            throw new InvalidSkeletonValueException(sprintf('Advanced routing path "%s" must use fail overwrite policy.', $path));
        }
        return $definition;
    }
}
