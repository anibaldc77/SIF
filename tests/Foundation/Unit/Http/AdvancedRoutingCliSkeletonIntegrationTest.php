<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectEntryPoint;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectManifest;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectPathDefinition;
use Sif\Foundation\ApplicationSkeleton\Template\AdvancedRoutingTemplateFactory;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectIdentifier;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectNamespace;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectPath;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOwnership;
use Sif\Foundation\Cli\Extension\AdvancedRoutingCommandContributor;
use Sif\Foundation\Http\Routing\Advanced\Compilation\RouteCompiler;
use Sif\Foundation\Http\Routing\RouteDefinition;
use Sif\Foundation\Http\Routing\RouteName;
use Sif\Foundation\Http\Value\HttpMethod;

final class AdvancedRoutingCliSkeletonIntegrationTest extends TestCase
{
    public function testRoutingContributorPublishesInspectionCommands(): void
    {
        $result = (new RouteCompiler())->compile([
            new RouteDefinition(new RouteName('health'), [HttpMethod::Get], '/health', 'health'),
        ]);
        self::assertTrue($result->successful());
        $table = $result->table();
        self::assertNotNull($table);

        $commands = (new AdvancedRoutingCommandContributor($table))->commands();
        self::assertSame(['route:cache:inspect', 'route:list'], array_map(
            static fn ($command): string => $command->metadata()->name()->value(),
            $commands,
        ));
    }

    public function testSkeletonTemplatesAreDeterministicAndUserOwned(): void
    {
        $manifest = new ProjectManifest(
            new ProjectIdentifier('routing-example'),
            'Routing Example',
            new ProjectNamespace('Example\\Routing'),
            '1.0.0',
            '1.0.0',
            '^2.0',
            '8.2.0',
            [new ProjectEntryPoint('http', new ProjectPath('public/index.php'))],
            ['local'],
            [
                new ProjectPathDefinition(new ProjectPath('public/index.php'), SkeletonOwnership::UserOwned),
                new ProjectPathDefinition(new ProjectPath('config/routing.php'), SkeletonOwnership::UserOwned),
                new ProjectPathDefinition(new ProjectPath('routes/advanced.php'), SkeletonOwnership::UserOwned),
            ],
            ['routing.advanced'],
        );

        $factory = new AdvancedRoutingTemplateFactory();
        $first = $factory->artifacts($manifest);
        $second = $factory->artifacts($manifest);
        self::assertSame(
            array_map(static fn ($artifact): array => $artifact->summary(), $first),
            array_map(static fn ($artifact): array => $artifact->summary(), $second),
        );
        self::assertStringContainsString("pathPrefix: '/api'", (string) $first[1]->content());
    }
}
