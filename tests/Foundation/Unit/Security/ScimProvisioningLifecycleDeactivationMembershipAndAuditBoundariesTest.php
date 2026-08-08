<?php
declare(strict_types=1);
namespace Sif\Tests\Foundation\Unit\Security;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\ScimMembershipConsistencyInterface;
use Sif\Foundation\Security\Contracts\ScimProvisioningEventPublisherInterface;
use Sif\Foundation\Security\Scim\Lifecycle\ScimLifecyclePlanner;
use Sif\Foundation\Security\Scim\Lifecycle\ScimLifecyclePolicy;
use Sif\Foundation\Security\Scim\Lifecycle\ScimProvisioningAction;
use Sif\Foundation\Security\Scim\Lifecycle\ScimProvisioningEvent;
use Sif\Foundation\Security\Scim\Lifecycle\ScimProvisioningTarget;

final class ScimProvisioningLifecycleDeactivationMembershipAndAuditBoundariesTest extends TestCase {
    public function testUserDeletionPlanOrdersDeactivateMembershipCleanupAndDelete(): void {
        $plan=(new ScimLifecyclePlanner(new ScimLifecyclePolicy()))->planUserDeletion();
        self::assertSame(
            [ScimProvisioningAction::DEACTIVATE,ScimProvisioningAction::REMOVE_MEMBERSHIP,ScimProvisioningAction::DELETE],
            array_map(static fn(ScimProvisioningAction $a): string=>$a->value(),$plan->actions())
        );
    }
    public function testGroupDeletionPlanCleansMembershipBeforeDelete(): void {
        $plan=(new ScimLifecyclePlanner(new ScimLifecyclePolicy()))->planGroupDeletion();
        self::assertSame(
            [ScimProvisioningAction::REMOVE_MEMBERSHIP,ScimProvisioningAction::DELETE],
            array_map(static fn(ScimProvisioningAction $a): string=>$a->value(),$plan->actions())
        );
    }
    public function testPolicyCanDisablePreDeleteSteps(): void {
        $plan=(new ScimLifecyclePlanner(new ScimLifecyclePolicy(false,false)))->planUserDeletion();
        self::assertCount(1,$plan->actions());
        self::assertSame(ScimProvisioningAction::DELETE,$plan->actions()[0]->value());
    }
    public function testProvisioningEventCarriesTypedContext(): void {
        $event=new ScimProvisioningEvent(
            new ScimProvisioningAction(ScimProvisioningAction::DEACTIVATE),
            new ScimProvisioningTarget(ScimProvisioningTarget::USER,'user-001'),
            new DateTimeImmutable('2026-08-08T16:00:00Z'),
            ['reason'=>'upstream-deactivation']
        );
        self::assertSame('user-001',$event->target()->resourceId());
        self::assertSame('upstream-deactivation',$event->context()['reason']);
    }
    public function testBoundariesRemainInfrastructureNeutral(): void {
        foreach ([ScimProvisioningEventPublisherInterface::class,ScimMembershipConsistencyInterface::class] as $class) {
            $r=new \ReflectionClass($class);
            $source=file_get_contents((string)$r->getFileName());
            self::assertIsString($source);
            self::assertStringNotContainsString('PDO',$source);
            self::assertStringNotContainsString('Redis',$source);
            self::assertStringNotContainsString('curl_',strtolower($source));
        }
    }
    public function testLifecycleRemainsProviderNeutral(): void {
        $r=new \ReflectionClass(ScimLifecyclePlanner::class);
        $source=file_get_contents((string)$r->getFileName());
        self::assertIsString($source);
        self::assertStringNotContainsString('Keycloak',$source);
        self::assertStringNotContainsString('Okta',$source);
    }
}
