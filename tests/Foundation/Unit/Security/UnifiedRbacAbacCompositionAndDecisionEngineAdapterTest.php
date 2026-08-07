<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Authentication\AuthenticationEvidence;
use Sif\Foundation\Security\Authentication\AuthenticationLevel;
use Sif\Foundation\Security\Authentication\AuthenticationMethod;
use Sif\Foundation\Security\Authorization\Attribute\AuthorizationAttributeBag;
use Sif\Foundation\Security\Authorization\Attribute\AuthorizationAttributeComparison;
use Sif\Foundation\Security\Authorization\Attribute\AuthorizationAttributeScope;
use Sif\Foundation\Security\Authorization\AuthorizationFailureReason;
use Sif\Foundation\Security\Authorization\Permission\EffectivePermissionResolver;
use Sif\Foundation\Security\Authorization\Permission\PermissionIdentifier;
use Sif\Foundation\Security\Authorization\Permission\PermissionSet;
use Sif\Foundation\Security\Authorization\Permission\PrincipalAuthorizationGrantResolver;
use Sif\Foundation\Security\Authorization\Policy\AbacAuthorizationPolicy;
use Sif\Foundation\Security\Authorization\Policy\AdvancedAuthorizationService;
use Sif\Foundation\Security\Authorization\Policy\CompositeAuthorizationPolicy;
use Sif\Foundation\Security\Authorization\Policy\CompositeAuthorizationPolicyEvaluator;
use Sif\Foundation\Security\Authorization\Policy\ExistingAuthorizationDecisionAdapter;
use Sif\Foundation\Security\Authorization\Policy\RbacAuthorizationPolicy;
use Sif\Foundation\Security\Authorization\Requirement\AttributeRequirement;
use Sif\Foundation\Security\Authorization\Requirement\AuthorizationRequirementSet;
use Sif\Foundation\Security\Authorization\Requirement\ContextualRequirementSet;
use Sif\Foundation\Security\Authorization\Requirement\PermissionRequirement;
use Sif\Foundation\Security\Authorization\Role\RoleDefinition;
use Sif\Foundation\Security\Authorization\Role\RoleHierarchy;
use Sif\Foundation\Security\Authorization\Role\RoleIdentifier;
use Sif\Foundation\Security\Authorization\Role\RoleSet;
use Sif\Foundation\Security\Contracts\AuthorizationAttributeProviderInterface;
use Sif\Foundation\Security\Contracts\PermissionResolverInterface;
use Sif\Foundation\Security\Contracts\RoleResolverInterface;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\Identity\Identity;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Identity\PrincipalAttributeCollection;

final class UnifiedRbacAbacCompositionAndDecisionEngineAdapterTest extends TestCase
{
    public function testCompositePolicyRequiresBothRbacAndAbac(): void
    {
        $policy = $this->compositePolicy();

        self::assertTrue($policy->isSatisfiedBy(
            $this->principal(),
            new AuthorizationAttributeBag(['owner.id' => 'advanced-user'])
        ));

        self::assertFalse($policy->isSatisfiedBy(
            $this->principal(),
            new AuthorizationAttributeBag(['owner.id' => 'someone-else'])
        ));
    }

    public function testEvaluatorProducesIntermediateEvaluationNotFrameworkDecision(): void
    {
        $evaluation = (new CompositeAuthorizationPolicyEvaluator(
            $this->compositePolicy()
        ))->evaluate(
            $this->principal(),
            new AuthorizationAttributeBag(['owner.id' => 'advanced-user'])
        );

        self::assertTrue($evaluation->isSatisfied());
        self::assertStringContainsString('satisfied', $evaluation->reason());
    }

    public function testAdapterMapsSatisfiedEvaluationToExistingAuthorizationDecision(): void
    {
        $decision = $this->service()->decide(
            $this->principal(),
            new AuthorizationAttributeBag(['owner.id' => 'advanced-user'])
        );

        self::assertTrue($decision->isAllowed());
        self::assertSame(AuthorizationFailureReason::NONE, $decision->reason());
    }

    public function testAdapterMapsRejectedEvaluationToExistingAuthorizationDecision(): void
    {
        $decision = $this->service()->decide(
            $this->principal(),
            new AuthorizationAttributeBag(['owner.id' => 'different-owner'])
        );

        self::assertFalse($decision->isAllowed());
        self::assertSame(
            AuthorizationFailureReason::NOT_AUTHORIZED,
            $decision->reason()
        );
    }

    public function testAdvancedServiceReturnsExistingAuthorizationDecision(): void
    {
        $reflection = new \ReflectionMethod(
            AdvancedAuthorizationService::class,
            'decide'
        );

        self::assertSame(
            \Sif\Foundation\Security\Authorization\AuthorizationDecision::class,
            (string) $reflection->getReturnType()
        );
    }

    public function testCompositeAuthorizationDoesNotMutateAuthenticationLevel(): void
    {
        $principal = $this->principal();

        $decision = $this->service()->decide(
            $principal,
            new AuthorizationAttributeBag(['owner.id' => 'advanced-user'])
        );

        self::assertTrue($decision->isAllowed());
        self::assertSame(50, $principal->evidence()->level()->value());
    }

    private function service(): AdvancedAuthorizationService
    {
        return new AdvancedAuthorizationService(
            new CompositeAuthorizationPolicyEvaluator(
                $this->compositePolicy()
            ),
            new ExistingAuthorizationDecisionAdapter()
        );
    }

    private function compositePolicy(): CompositeAuthorizationPolicy
    {
        $roles = new class implements RoleResolverInterface {
            public function resolve(
                AuthenticatedPrincipal $principal
            ): RoleSet {
                return new RoleSet([new RoleIdentifier('manager')]);
            }
        };

        $permissions = new class implements PermissionResolverInterface {
            public function resolve(
                AuthenticatedPrincipal $principal
            ): PermissionSet {
                return new PermissionSet();
            }
        };

        $hierarchy = new RoleHierarchy([
            new RoleDefinition(
                new RoleIdentifier('manager'),
                new PermissionSet([
                    new PermissionIdentifier('invoice.approve'),
                ])
            ),
        ]);

        $rbac = new RbacAuthorizationPolicy(
            new PrincipalAuthorizationGrantResolver(
                $roles,
                $permissions,
                new EffectivePermissionResolver($hierarchy)
            ),
            new AuthorizationRequirementSet([
                new PermissionRequirement(
                    new PermissionIdentifier('invoice.approve')
                ),
            ])
        );

        $attributes = new class implements AuthorizationAttributeProviderInterface {
            public function provide(
                AuthenticatedPrincipal $principal
            ): AuthorizationAttributeBag {
                return new AuthorizationAttributeBag([
                    'department' => 'finance',
                ]);
            }
        };

        $abac = new AbacAuthorizationPolicy(
            $attributes,
            new ContextualRequirementSet([
                new AttributeRequirement(
                    AuthorizationAttributeScope::Subject,
                    'department',
                    AuthorizationAttributeComparison::Equals,
                    'finance'
                ),
                new AttributeRequirement(
                    AuthorizationAttributeScope::Resource,
                    'owner.id',
                    AuthorizationAttributeComparison::Equals,
                    'advanced-user'
                ),
            ])
        );

        return new CompositeAuthorizationPolicy($rbac, $abac);
    }

    private function principal(): AuthenticatedPrincipal
    {
        return new AuthenticatedPrincipal(
            new Identity(new IdentityId('advanced-user')),
            new PrincipalAttributeCollection(),
            new AuthenticationEvidence(
                new AuthenticationMethod('password'),
                new AuthenticationLevel(50),
                new \DateTimeImmutable('2026-08-07T16:00:00+00:00')
            )
        );
    }
}
