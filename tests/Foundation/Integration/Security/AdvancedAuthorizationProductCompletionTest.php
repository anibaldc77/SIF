<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Integration\Security;

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
use Sif\Foundation\Security\Authorization\Integration\AdvancedAuthorizationGuard;
use Sif\Foundation\Security\Authorization\Integration\AdvancedAuthorizationRequest;
use Sif\Foundation\Security\Authorization\Integration\ControllerAuthorizationBridge;
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
use Sif\Foundation\Security\Authorization\Requirement\RoleRequirement;
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

final class AdvancedAuthorizationProductCompletionTest extends TestCase
{
    public function testEndToEndRbacAndAbacAllowWhenAllRequirementsMatch(): void
    {
        $decision = $this->guard()->decide(
            new AdvancedAuthorizationRequest(
                $this->principal(),
                new AuthorizationAttributeBag([
                    'owner.id' => 'product-user',
                    'tenant.id' => 7,
                ]),
                new AuthorizationAttributeBag([
                    'maintenance' => false,
                ])
            )
        );

        self::assertTrue($decision->isAllowed());
    }

    public function testEndToEndFailsClosedWhenAbacContextDoesNotMatch(): void
    {
        $decision = $this->guard()->decide(
            new AdvancedAuthorizationRequest(
                $this->principal(),
                new AuthorizationAttributeBag([
                    'owner.id' => 'different-user',
                    'tenant.id' => 7,
                ]),
                new AuthorizationAttributeBag([
                    'maintenance' => false,
                ])
            )
        );

        self::assertFalse($decision->isAllowed());
    }

    public function testRoleInheritanceContributesEffectivePermissionWithoutBecomingDecision(): void
    {
        $grants = $this->grantResolver()->resolve($this->principal());

        self::assertSame(['manager'], $grants->roles()->values());
        self::assertSame(
            ['audit.read', 'invoice.approve', 'invoice.read'],
            $grants->permissions()->values()
        );
    }

    public function testCachedGrantsDoNotCacheContextualDecision(): void
    {
        $diagnostics = $this->diagnostics();
        $principal = $this->principal();

        $allowed = $diagnostics->evaluate(
            $principal,
            new AuthorizationAttributeBag([
                'owner.id' => 'product-user',
                'tenant.id' => 7,
            ]),
            new AuthorizationAttributeBag([
                'maintenance' => false,
            ])
        );

        $denied = $diagnostics->evaluate(
            $principal,
            new AuthorizationAttributeBag([
                'owner.id' => 'other',
                'tenant.id' => 7,
            ]),
            new AuthorizationAttributeBag([
                'maintenance' => false,
            ])
        );

        self::assertTrue($allowed->decision()->isAllowed());
        self::assertFalse($denied->decision()->isAllowed());
    }

    public function testDiagnosticsStaySanitizedAndDeterministic(): void
    {
        $snapshot = $this->diagnostics()->evaluate(
            $this->principal(),
            new AuthorizationAttributeBag([
                'owner.id' => 'product-user',
                'tenant.id' => 7,
                'classification' => 'confidential',
            ]),
            new AuthorizationAttributeBag([
                'maintenance' => false,
            ])
        )->toArray();

        $encoded = json_encode($snapshot, JSON_THROW_ON_ERROR);

        self::assertStringContainsString('identity_fingerprint', $encoded);
        self::assertStringContainsString('evaluation_fingerprint', $encoded);
        self::assertStringNotContainsString('product-user', $encoded);
        self::assertStringNotContainsString('confidential', $encoded);
    }

    public function testControllerBridgeReturnsCanonicalDecisionWithoutHttpPolicyLeakage(): void
    {
        $bridge = new ControllerAuthorizationBridge($this->guard());

        $decision = $bridge->authorize(
            new AdvancedAuthorizationRequest(
                $this->principal(),
                new AuthorizationAttributeBag([
                    'owner.id' => 'product-user',
                    'tenant.id' => 7,
                ]),
                new AuthorizationAttributeBag([
                    'maintenance' => false,
                ])
            )
        );

        self::assertTrue($decision->isAllowed());
        self::assertSame(
            \Sif\Foundation\Security\Authorization\AuthorizationDecision::class,
            $decision::class
        );
    }

    private function guard(): AdvancedAuthorizationGuard
    {
        return new AdvancedAuthorizationGuard(
            $this->authorization()
        );
    }

    private function diagnostics(): AdvancedAuthorizationDiagnosticService
    {
        return new AdvancedAuthorizationDiagnosticService(
            $this->authorization(),
            new CachedPrincipalAuthorizationGrantResolver(
                $this->grantResolver(),
                new InMemoryAuthorizationGrantCache()
            )
        );
    }

    private function authorization(): AdvancedAuthorizationService
    {
        $rbac = new RbacAuthorizationPolicy(
            $this->grantResolver(),
            new AuthorizationRequirementSet([
                new RoleRequirement(
                    new RoleIdentifier('manager')
                ),
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
                    'tenant.id' => 7,
                ]);
            }
        };

        $abac = new AbacAuthorizationPolicy(
            $provider,
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
                    'product-user'
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
                return new RoleSet([
                    new RoleIdentifier('manager'),
                ]);
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
            new EffectivePermissionResolver(
                new RoleHierarchy([
                    new RoleDefinition(
                        new RoleIdentifier('auditor'),
                        new PermissionSet([
                            new PermissionIdentifier('audit.read'),
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
                ])
            )
        );
    }

    private function principal(): AuthenticatedPrincipal
    {
        return new AuthenticatedPrincipal(
            new Identity(new IdentityId('product-user')),
            new PrincipalAttributeCollection(),
            new AuthenticationEvidence(
                new AuthenticationMethod('password'),
                new AuthenticationLevel(50),
                new \DateTimeImmutable('2026-08-07T19:00:00+00:00')
            )
        );
    }
}
