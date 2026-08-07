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
use Sif\Foundation\Security\Authorization\Cache\CachedPrincipalAuthorizationGrantResolver;
use Sif\Foundation\Security\Authorization\Cache\InMemoryAuthorizationGrantCache;
use Sif\Foundation\Security\Authorization\Diagnostics\AdvancedAuthorizationDiagnosticService;
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

final class AuthorizationGrantCacheAndDeterministicDiagnosticsTest extends TestCase
{
    public function testGrantCacheAvoidsRepeatedRoleAndPermissionResolution(): void
    {
        $roleCalls = 0;
        $permissionCalls = 0;

        $roles = new class($roleCalls) implements RoleResolverInterface {
            public function __construct(private int &$calls)
            {
            }

            public function resolve(AuthenticatedPrincipal $principal): RoleSet
            {
                $this->calls++;

                return new RoleSet([new RoleIdentifier('manager')]);
            }
        };

        $permissions = new class($permissionCalls) implements PermissionResolverInterface {
            public function __construct(private int &$calls)
            {
            }

            public function resolve(AuthenticatedPrincipal $principal): PermissionSet
            {
                $this->calls++;

                return new PermissionSet([
                    new PermissionIdentifier('profile.self.edit'),
                ]);
            }
        };

        $resolver = new CachedPrincipalAuthorizationGrantResolver(
            new PrincipalAuthorizationGrantResolver(
                $roles,
                $permissions,
                new EffectivePermissionResolver($this->hierarchy())
            ),
            new InMemoryAuthorizationGrantCache()
        );

        $principal = $this->principal();

        $first = $resolver->resolve($principal);
        $second = $resolver->resolve($principal);

        self::assertSame($first->permissions()->values(), $second->permissions()->values());
        self::assertSame(1, $roleCalls);
        self::assertSame(1, $permissionCalls);
    }

    public function testInvalidationForcesFreshGrantResolution(): void
    {
        $cache = new InMemoryAuthorizationGrantCache();
        $inner = $this->grantResolver();
        $resolver = new CachedPrincipalAuthorizationGrantResolver($inner, $cache);
        $principal = $this->principal();

        $first = $resolver->resolve($principal);
        $resolver->invalidate($principal);
        $second = $resolver->resolve($principal);

        self::assertSame(
            $first->permissions()->values(),
            $second->permissions()->values()
        );
        self::assertNotSame($first, $second);
    }

    public function testCacheIsScopedByIdentity(): void
    {
        $resolver = new CachedPrincipalAuthorizationGrantResolver(
            $this->grantResolver(),
            new InMemoryAuthorizationGrantCache()
        );

        $first = $resolver->resolve($this->principal('user-a'));
        $second = $resolver->resolve($this->principal('user-b'));

        self::assertNotSame($first, $second);
    }

    public function testDiagnosticsExposeFingerprintsAndCountsButNotAttributeValues(): void
    {
        $diagnostic = $this->diagnosticService()->evaluate(
            $this->principal(),
            new AuthorizationAttributeBag([
                'owner.id' => 'diagnostic-user',
                'classification' => 'highly-sensitive',
            ])
        );

        $encoded = json_encode($diagnostic->toArray(), JSON_THROW_ON_ERROR);

        self::assertStringContainsString('identity_fingerprint', $encoded);
        self::assertStringContainsString('evaluation_fingerprint', $encoded);
        self::assertStringNotContainsString('diagnostic-user', $encoded);
        self::assertStringNotContainsString('highly-sensitive', $encoded);
    }

    public function testDiagnosticsAreDeterministicForEquivalentAuthorizationInputs(): void
    {
        $service = $this->diagnosticService();
        $principal = $this->principal();

        $first = $service->evaluate(
            $principal,
            new AuthorizationAttributeBag([
                'owner.id' => 'diagnostic-user',
            ])
        )->toArray();

        $second = $service->evaluate(
            $principal,
            new AuthorizationAttributeBag([
                'owner.id' => 'diagnostic-user',
            ])
        )->toArray();

        self::assertSame(
            $first['evaluation_fingerprint'],
            $second['evaluation_fingerprint']
        );
        self::assertSame($first['allowed'], $second['allowed']);
    }

    public function testDecisionIsNeverCachedAcrossDifferentResourceContexts(): void
    {
        $service = $this->diagnosticService();
        $principal = $this->principal();

        $allowed = $service->evaluate(
            $principal,
            new AuthorizationAttributeBag([
                'owner.id' => 'diagnostic-user',
            ])
        );

        $denied = $service->evaluate(
            $principal,
            new AuthorizationAttributeBag([
                'owner.id' => 'other-owner',
            ])
        );

        self::assertTrue($allowed->decision()->isAllowed());
        self::assertFalse($denied->decision()->isAllowed());
    }

    private function diagnosticService(): AdvancedAuthorizationDiagnosticService
    {
        $cached = new CachedPrincipalAuthorizationGrantResolver(
            $this->grantResolver(),
            new InMemoryAuthorizationGrantCache()
        );

        return new AdvancedAuthorizationDiagnosticService(
            $this->advancedAuthorizationService(),
            $cached
        );
    }

    private function advancedAuthorizationService(): AdvancedAuthorizationService
    {
        $rbac = new RbacAuthorizationPolicy(
            $this->grantResolver(),
            new AuthorizationRequirementSet([
                new PermissionRequirement(
                    new PermissionIdentifier('invoice.approve')
                ),
            ])
        );

        $provider = new class implements AuthorizationAttributeProviderInterface {
            public function provide(
                AuthenticatedPrincipal $principal
            ): AuthorizationAttributeBag {
                return new AuthorizationAttributeBag([
                    'department' => 'finance',
                ]);
            }
        };

        $abac = new AbacAuthorizationPolicy(
            $provider,
            new ContextualRequirementSet([
                new AttributeRequirement(
                    AuthorizationAttributeScope::Resource,
                    'owner.id',
                    AuthorizationAttributeComparison::Equals,
                    'diagnostic-user'
                ),
            ])
        );

        return new AdvancedAuthorizationService(
            new CompositeAuthorizationPolicyEvaluator(
                new CompositeAuthorizationPolicy($rbac, $abac)
            ),
            new ExistingAuthorizationDecisionAdapter()
        );
    }

    private function grantResolver(): PrincipalAuthorizationGrantResolver
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

        return new PrincipalAuthorizationGrantResolver(
            $roles,
            $permissions,
            new EffectivePermissionResolver($this->hierarchy())
        );
    }

    private function hierarchy(): RoleHierarchy
    {
        return new RoleHierarchy([
            new RoleDefinition(
                new RoleIdentifier('manager'),
                new PermissionSet([
                    new PermissionIdentifier('invoice.approve'),
                ])
            ),
        ]);
    }

    private function principal(
        string $id = 'diagnostic-user'
    ): AuthenticatedPrincipal {
        return new AuthenticatedPrincipal(
            new Identity(new IdentityId($id)),
            new PrincipalAttributeCollection(),
            new AuthenticationEvidence(
                new AuthenticationMethod('password'),
                new AuthenticationLevel(50),
                new \DateTimeImmutable('2026-08-07T17:00:00+00:00')
            )
        );
    }
}
