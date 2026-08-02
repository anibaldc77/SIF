<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Template;

use Sif\Foundation\ApplicationSkeleton\Exceptions\InvalidSkeletonValueException;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonArtifact;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonArtifactType;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectManifest;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectPathDefinition;
use Sif\Foundation\ApplicationSkeleton\Value\ApplicationCodeName;
use Sif\Foundation\ApplicationSkeleton\Value\MigrationTemplateName;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOverwritePolicy;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOwnership;

final readonly class ApplicationCodeTemplateFactory
{
    public function __construct(private ApplicationTemplateRenderer $renderer = new ApplicationTemplateRenderer())
    {
    }

    public function moduleServiceProvider(ProjectManifest $manifest, ApplicationCodeName $module): SkeletonArtifact
    {
        $path = sprintf('app/Modules/%s/%sServiceProvider.php', $module->value(), $module->value());
        $definition = $this->requireUserOwnedPath($manifest, $path);

        $template = new ApplicationTemplate('module-service-provider', <<<'TPL'
<?php

declare(strict_types=1);

namespace {{project_namespace}}\Modules\{{module_name}};

use Sif\Foundation\ServiceProvider;

final class {{module_name}}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
    }
}
TPL
        );

        return new SkeletonArtifact(
            $definition,
            SkeletonArtifactType::File,
            $this->renderer->render($template, [
                'project_namespace' => $manifest->namespace()->value(),
                'module_name' => $module->value(),
            ]) . "\n",
        );
    }

    public function model(
        ProjectManifest $manifest,
        ApplicationCodeName $model,
        string $table,
        ModelTemplateOptions $options = new ModelTemplateOptions(),
    ): SkeletonArtifact {
        if (preg_match('/^[a-z][a-z0-9_]*$/', $table) !== 1) {
            throw new InvalidSkeletonValueException(sprintf('Invalid model table "%s".', $table));
        }

        $path = sprintf('app/Models/%s.php', $model->value());
        $definition = $this->requireUserOwnedPath($manifest, $path);
        $identity = implode("',\n            '", $options->identityColumns());
        $timestamps = $options->timestamps() ? 'true' : 'false';
        $softDeletes = $options->softDeletes() ? 'true' : 'false';

        $template = new ApplicationTemplate('basemodel', <<<'TPL'
<?php

declare(strict_types=1);

namespace {{project_namespace}}\Models;

use Sif\Foundation\Model\BaseModel;

final class {{model_name}} extends BaseModel
{
    public const TABLE = '{{table_name}}';

    /** @var list<string> */
    public const IDENTITY = [
            '{{identity_columns}}',
    ];

    public const USE_TIMESTAMPS = {{use_timestamps}};
    public const USE_SOFT_DELETES = {{use_soft_deletes}};
}
TPL
        );

        return new SkeletonArtifact(
            $definition,
            SkeletonArtifactType::File,
            $this->renderer->render($template, [
                'project_namespace' => $manifest->namespace()->value(),
                'model_name' => $model->value(),
                'table_name' => $table,
                'identity_columns' => $identity,
                'use_timestamps' => $timestamps,
                'use_soft_deletes' => $softDeletes,
            ]) . "\n",
        );
    }

    public function migration(
        ProjectManifest $manifest,
        MigrationTemplateName $migration,
        ApplicationCodeName $className,
    ): SkeletonArtifact {
        $path = sprintf('database/migrations/%s.php', $migration->value());
        $definition = $this->requireUserOwnedPath($manifest, $path);

        $template = new ApplicationTemplate('migration', <<<'TPL'
<?php

declare(strict_types=1);

use Sif\Foundation\Migration\Contracts\MigrationInterface;
use Sif\Foundation\Migration\MigrationContext;

return new class implements MigrationInterface {
    public function id(): string
    {
        return '{{migration_id}}';
    }

    public function up(MigrationContext $context): void
    {
        // Define the forward operations for {{class_name}}.
    }

    public function down(MigrationContext $context): void
    {
        // Define the compensating operations for {{class_name}}.
    }
};
TPL
        );

        return new SkeletonArtifact(
            $definition,
            SkeletonArtifactType::File,
            $this->renderer->render($template, [
                'migration_id' => $migration->value(),
                'class_name' => $className->value(),
            ]) . "\n",
        );
    }

    private function requireUserOwnedPath(ProjectManifest $manifest, string $path): ProjectPathDefinition
    {
        $definition = $manifest->paths()[$path] ?? null;
        if (!$definition instanceof ProjectPathDefinition) {
            throw new InvalidSkeletonValueException(sprintf(
                'Application code path "%s" is not declared by the project manifest.',
                $path,
            ));
        }

        if ($definition->ownership() !== SkeletonOwnership::UserOwned) {
            throw new InvalidSkeletonValueException(sprintf(
                'Application code path "%s" must be user-owned.',
                $path,
            ));
        }

        if ($definition->overwritePolicy() !== SkeletonOverwritePolicy::Fail) {
            throw new InvalidSkeletonValueException(sprintf(
                'Application code path "%s" must use the fail overwrite policy.',
                $path,
            ));
        }

        return $definition;
    }
}
