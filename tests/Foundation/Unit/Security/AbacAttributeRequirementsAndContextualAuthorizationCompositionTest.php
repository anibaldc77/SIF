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
use Sif\Foundation\Security\Authorization\Policy\AbacAuthorizationPolicy;
use Sif\Foundation\Security\Authorization\Requirement\AttributeRequirement;
use Sif\Foundation\Security\Authorization\Requirement\ContextualRequirementSet;
use Sif\Foundation\Security\Contracts\AuthorizationAttributeProviderInterface;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\Identity\Identity;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Identity\PrincipalAttributeCollection;

final class AbacAttributeRequirementsAndContextualAuthorizationCompositionTest extends TestCase
{
    public function testSubjectAttributeRequirementUsesProviderData(): void
    {
        $policy = $this->policy([
            new AttributeRequirement(
                AuthorizationAttributeScope::Subject,
                'department',
                AuthorizationAttributeComparison::Equals,
                'finance'
            ),
        ]);

        self::assertTrue(
            $policy->isSatisfiedBy($this->principal())
        );
    }

    public function testResourceOwnershipRequirementUsesExplicitResourceContext(): void
    {
        $policy = $this->policy([
            new AttributeRequirement(
                AuthorizationAttributeScope::Resource,
                'owner.id',
                AuthorizationAttributeComparison::Equals,
                'abac-user'
            ),
        ]);

        self::assertTrue(
            $policy->isSatisfiedBy(
                $this->principal(),
                new AuthorizationAttributeBag([
                    'owner.id' => 'abac-user',
                ])
            )
        );
    }

    public function testMissingAttributeFailsClosed(): void
    {
        $policy = $this->policy([
            new AttributeRequirement(
                AuthorizationAttributeScope::Resource,
                'classification',
                AuthorizationAttributeComparison::Equals,
                'internal'
            ),
        ]);

        self::assertFalse(
            $policy->isSatisfiedBy($this->principal())
        );
    }

    public function testEnvironmentNumericComparisonIsTypeSafe(): void
    {
        $policy = $this->policy([
            new AttributeRequirement(
                AuthorizationAttributeScope::Environment,
                'risk.score',
                AuthorizationAttributeComparison::LessThanOrEqual,
                30
            ),
        ]);

        self::assertTrue(
            $policy->isSatisfiedBy(
                $this->principal(),
                new AuthorizationAttributeBag(),
                new AuthorizationAttributeBag([
                    'risk.score' => 15,
                ])
            )
        );

        self::assertFalse(
            $policy->isSatisfiedBy(
                $this->principal(),
                new AuthorizationAttributeBag(),
                new AuthorizationAttributeBag([
                    'risk.score' => '15',
                ])
            )
        );
    }

    public function testMultipleContextualRequirementsComposeAsAllOfFailClosed(): void
    {
        $policy = $this->policy([
            new AttributeRequirement(
                AuthorizationAttributeScope::Subject,
                'tenant.id',
                AuthorizationAttributeComparison::Equals,
                7
            ),
            new AttributeRequirement(
                AuthorizationAttributeScope::Resource,
                'tenant.id',
                AuthorizationAttributeComparison::Equals,
                7
            ),
            new AttributeRequirement(
                AuthorizationAttributeScope::Environment,
                'maintenance',
                AuthorizationAttributeComparison::Equals,
                false
            ),
        ]);

        self::assertTrue(
            $policy->isSatisfiedBy(
                $this->principal(),
                new AuthorizationAttributeBag([
                    'tenant.id' => 7,
                ]),
                new AuthorizationAttributeBag([
                    'maintenance' => false,
                ])
            )
        );

        self::assertFalse(
            $policy->isSatisfiedBy(
                $this->principal(),
                new AuthorizationAttributeBag([
                    'tenant.id' => 8,
                ]),
                new AuthorizationAttributeBag([
                    'maintenance' => false,
                ])
            )
        );
    }

    public function testAbacPolicyDoesNotCreateDecisionOrMutateAuthentication(): void
    {
        $policy = $this->policy([]);
        $principal = $this->principal();

        self::assertTrue($policy->isSatisfiedBy($principal));
        self::assertSame(50, $principal->evidence()->level()->value());

        $source = file_get_contents(
            dirname(__DIR__, 4)
            . '/src/Foundation/Security/Authorization/Policy/'
            . 'AbacAuthorizationPolicy.php'
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('AuthorizationDecision', $source);
        self::assertStringNotContainsString('SecurityContext', $source);
        self::assertStringNotContainsString('Session', $source);
        self::assertStringNotContainsString('allow(', $source);
        self::assertStringNotContainsString('deny(', $source);
    }

    /**
     * @param list<AttributeRequirement> $requirements
     */
    private function policy(array $requirements): AbacAuthorizationPolicy
    {
        $provider = new class implements AuthorizationAttributeProviderInterface {
            public function provide(
                AuthenticatedPrincipal $principal
            ): AuthorizationAttributeBag {
                return new AuthorizationAttributeBag([
                    'department' => 'finance',
                    'tenant.id' => 7,
                ]);
            }
        };

        return new AbacAuthorizationPolicy(
            $provider,
            new ContextualRequirementSet($requirements)
        );
    }

    private function principal(): AuthenticatedPrincipal
    {
        return new AuthenticatedPrincipal(
            new Identity(new IdentityId('abac-user')),
            new PrincipalAttributeCollection(),
            new AuthenticationEvidence(
                new AuthenticationMethod('password'),
                new AuthenticationLevel(50),
                new \DateTimeImmutable('2026-08-07T15:00:00+00:00')
            )
        );
    }
}
