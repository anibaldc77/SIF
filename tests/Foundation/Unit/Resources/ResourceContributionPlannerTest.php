<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Resources;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Modules\ModuleId;
use Sif\Foundation\Resources\Contribution\DeterministicResourceContributionPlanner;
use Sif\Foundation\Resources\Contribution\ModuleResourceContribution;
use Sif\Foundation\Resources\Contribution\PlannedResourceContribution;
use Sif\Foundation\Resources\Contribution\ResourceOverridePolicy;
use Sif\Foundation\Resources\Exceptions\InvalidResourceContributionOrderException;
use Sif\Foundation\Resources\Exceptions\InvalidResourceOverridePolicyException;
use Sif\Foundation\Resources\Exceptions\ResourceOverrideConflictException;
use Sif\Foundation\Resources\ResourceDescriptor;
use Sif\Foundation\Resources\ResourceIdentifier;
use Sif\Foundation\Resources\ResourceNamespace;
use Sif\Foundation\Resources\ResourcePath;
use Sif\Foundation\Resources\ResourcePriority;
use Sif\Foundation\Resources\ResourceType;

final class ResourceContributionPlannerTest extends TestCase
{
    public function test_compiles_unique_contributions(): void
    {
        $plan = (new DeterministicResourceContributionPlanner())->compile([
            $this->contribution('module.a', 'a', 0),
            $this->contribution('module.b', 'b', 10),
        ]);

        self::assertSame(['b', 'a'], array_map(
            static fn (ResourceDescriptor $resource): string => $resource->identifier()->value(),
            $plan->resources(),
        ));
        self::assertSame(2, $plan->count());
        self::assertSame([], $plan->overrideDecisions());
    }

    public function test_forbid_policy_rejects_collision(): void
    {
        $this->expectException(ResourceOverrideConflictException::class);

        (new DeterministicResourceContributionPlanner())->compile([
            $this->contribution('module.a', 'main', 0),
            $this->contribution('module.b', 'main', 20),
        ]);
    }

    public function test_higher_priority_policy_replaces_lower_priority_resource(): void
    {
        $plan = (new DeterministicResourceContributionPlanner())->compile([
            $this->contribution('module.a', 'main', 0),
            $this->contribution('module.b', 'main', 20, ResourceOverridePolicy::replaceIfHigherPriority()),
        ]);

        self::assertSame('module.b', $plan->effectiveContributions()[0]->contribution()->moduleId()->value());
        self::assertCount(1, $plan->overrideDecisions());
        self::assertSame('module.a', $plan->overrideDecisions()[0]->replaced()->contribution()->moduleId()->value());
    }

    public function test_higher_priority_policy_rejects_equal_priority(): void
    {
        $this->expectException(ResourceOverrideConflictException::class);

        (new DeterministicResourceContributionPlanner())->compile([
            $this->contribution('module.a', 'main', 10),
            $this->contribution('module.b', 'main', 10, ResourceOverridePolicy::replaceIfHigherPriority()),
        ]);
    }

    public function test_higher_priority_policy_rejects_lower_priority(): void
    {
        $this->expectException(ResourceOverrideConflictException::class);

        (new DeterministicResourceContributionPlanner())->compile([
            $this->contribution('module.a', 'main', 20),
            $this->contribution('module.b', 'main', 10, ResourceOverridePolicy::replaceIfHigherPriority()),
        ]);
    }

    public function test_replace_always_replaces_regardless_of_priority(): void
    {
        $plan = (new DeterministicResourceContributionPlanner())->compile([
            $this->contribution('module.a', 'main', 100),
            $this->contribution('module.b', 'main', -100, ResourceOverridePolicy::replaceAlways()),
        ]);

        self::assertSame('module.b', $plan->effectiveContributions()[0]->contribution()->moduleId()->value());
    }

    public function test_distinct_namespaces_do_not_collide(): void
    {
        $planner = new DeterministicResourceContributionPlanner();
        $first = $this->contribution('module.a', 'main', 0, namespace: 'app');
        $second = $this->contribution('module.b', 'main', 0, namespace: 'admin');

        self::assertSame(2, $planner->compile([$first, $second])->count());
    }

    public function test_equal_priority_preserves_original_contribution_order(): void
    {
        $plan = (new DeterministicResourceContributionPlanner())->compile([
            $this->contribution('module.a', 'first', 10),
            $this->contribution('module.b', 'second', 10),
        ]);

        self::assertSame(['first', 'second'], array_map(
            static fn (ResourceDescriptor $resource): string => $resource->identifier()->value(),
            $plan->resources(),
        ));
    }

    public function test_invalid_override_policy_is_rejected(): void
    {
        $this->expectException(InvalidResourceOverridePolicyException::class);
        new ResourceOverridePolicy('unknown');
    }

    public function test_negative_contribution_order_is_rejected(): void
    {
        $this->expectException(InvalidResourceContributionOrderException::class);
        new PlannedResourceContribution($this->contribution('module.a', 'main', 0), -1);
    }

    private function contribution(
        string $module,
        string $identifier,
        int $priority,
        ?ResourceOverridePolicy $policy = null,
        string $namespace = 'application',
    ): ModuleResourceContribution {
        return new ModuleResourceContribution(
            new ModuleId($module),
            new ResourceDescriptor(
                new ResourceIdentifier($identifier),
                new ResourceNamespace($namespace),
                new ResourceType(ResourceType::STYLESHEET),
                new ResourcePath('assets/' . $identifier . '.css'),
                new ResourcePriority($priority),
            ),
            $policy ?? ResourceOverridePolicy::forbid(),
        );
    }
}
