<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Authentication\AuthenticationEvidence;
use Sif\Foundation\Security\Authentication\AuthenticationLevel;
use Sif\Foundation\Security\Authentication\AuthenticationMethod;
use Sif\Foundation\Security\Authorization\Permission\EffectivePermissionResolver;
use Sif\Foundation\Security\Authorization\Permission\PermissionIdentifier;
use Sif\Foundation\Security\Authorization\Permission\PermissionSet;
use Sif\Foundation\Security\Authorization\Permission\PrincipalAuthorizationGrantResolver;
use Sif\Foundation\Security\Authorization\Policy\RbacAuthorizationPolicy;
use Sif\Foundation\Security\Authorization\Requirement\AllOfRequirement;
use Sif\Foundation\Security\Authorization\Requirement\AnyOfRequirement;
use Sif\Foundation\Security\Authorization\Requirement\AuthorizationRequirementSet;
use Sif\Foundation\Security\Authorization\Requirement\PermissionRequirement;
use Sif\Foundation\Security\Authorization\Requirement\RoleRequirement;
use Sif\Foundation\Security\Authorization\Role\RoleDefinition;
use Sif\Foundation\Security\Authorization\Role\RoleHierarchy;
use Sif\Foundation\Security\Authorization\Role\RoleIdentifier;
use Sif\Foundation\Security\Authorization\Role\RoleSet;
use Sif\Foundation\Security\Contracts\PermissionResolverInterface;
use Sif\Foundation\Security\Contracts\RoleResolverInterface;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\Identity\Identity;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Identity\PrincipalAttributeCollection;

final class AuthorizationRequirementsAndRbacPolicyCompositionTest extends TestCase
{
    public function testPermissionRequirementUsesEffectivePermissions(): void
    {
        $policy = $this->policy(
            new AuthorizationRequirementSet([
                new PermissionRequirement(
                    new PermissionIdentifier('invoice.read')
                ),
            ])
        );

        self::assertTrue($policy->isSatisfiedBy($this->principal()));
    }

    public function testRoleRequirementUsesResolvedRolesOnly(): void
    {
        $policy = $this->policy(
            new AuthorizationRequirementSet([
                new RoleRequirement(
                    new RoleIdentifier('manager')
                ),
            ])
        );

        self::assertTrue($policy->isSatisfiedBy($this->principal()));

        $auditorOnly = $this->policy(
            new AuthorizationRequirementSet([
                new RoleRequirement(
                    new RoleIdentifier('auditor')
                ),
            ])
        );

        self::assertFalse($auditorOnly->isSatisfiedBy($this->principal()));
    }

    public function testAllOfCompositionFailsClosedWhenOneRequirementIsMissing(): void
    {
        $policy = $this->policy(
            new AuthorizationRequirementSet([
                new AllOfRequirement([
                    new PermissionRequirement(
                        new PermissionIdentifier('invoice.read')
                    ),
                    new PermissionRequirement(
                        new PermissionIdentifier('system.configure')
                    ),
                ]),
            ])
        );

        self::assertFalse($policy->isSatisfiedBy($this->principal()));
    }

    public function testAnyOfCompositionAllowsAnyExplicitSatisfiedRequirement(): void
    {
        $policy = $this->policy(
            new AuthorizationRequirementSet([
                new AnyOfRequirement([
                    new PermissionRequirement(
                        new PermissionIdentifier('system.configure')
                    ),
                    new PermissionRequirement(
                        new PermissionIdentifier('invoice.approve')
                    ),
                ]),
            ])
        );

        self::assertTrue($policy->isSatisfiedBy($this->principal()));
    }

    public function testEmptyRequirementSetIsNeutralAndDoesNotCreateAuthorizationDecision(): void
    {
        $policy = $this->policy(
            new AuthorizationRequirementSet()
        );

        self::assertTrue($policy->isSatisfiedBy($this->principal()));

        $source = file_get_contents(
            dirname(__DIR__, 4)
            . '/src/Foundation/Security/Authorization/Policy/'
            . 'RbacAuthorizationPolicy.php'
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('AuthorizationDecision', $source);
        self::assertStringNotContainsString('allow(', $source);
        self::assertStringNotContainsString('deny(', $source);
    }

    public function testNestedRequirementContainersRejectEmptyAmbiguousComposition(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new AllOfRequirement([]);
    }

    private function policy(
        AuthorizationRequirementSet $requirements
    ): RbacAuthorizationPolicy {
        $roles = new class implements RoleResolverInterface {
            public function resolve(
                AuthenticatedPrincipal $principal
            ): RoleSet {
                return new RoleSet([
                    new RoleIdentifier('manager'),
                ]);
            }
        };

        $permissions = new class implements PermissionResolverInterface {
            public function resolve(
                AuthenticatedPrincipal $principal
            ): PermissionSet {
                return new PermissionSet([
                    new PermissionIdentifier('profile.self.edit'),
                ]);
            }
        };

        $hierarchy = new RoleHierarchy([
            new RoleDefinition(
                new RoleIdentifier('auditor'),
                new PermissionSet([
                    new PermissionIdentifier('invoice.read'),
                ])
            ),
            new RoleDefinition(
                new RoleIdentifier('manager'),
                new PermissionSet([
                    new PermissionIdentifier('invoice.approve'),
                ]),
                new RoleSet([
                    new RoleIdentifier('auditor'),
                ])
            ),
        ]);

        return new RbacAuthorizationPolicy(
            new PrincipalAuthorizationGrantResolver(
                $roles,
                $permissions,
                new EffectivePermissionResolver($hierarchy)
            ),
            $requirements
        );
    }

    private function principal(): AuthenticatedPrincipal
    {
        return new AuthenticatedPrincipal(
            new Identity(new IdentityId('rbac-user')),
            new PrincipalAttributeCollection(),
            new AuthenticationEvidence(
                new AuthenticationMethod('password'),
                new AuthenticationLevel(50),
                new \DateTimeImmutable('2026-08-07T14:00:00+00:00')
            )
        );
    }
}
