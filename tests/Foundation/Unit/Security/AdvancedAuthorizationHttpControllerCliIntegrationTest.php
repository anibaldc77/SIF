<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Cli\Command\Security\AdvancedAuthorizationInspectCommand;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliInvocation;
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

final class AdvancedAuthorizationHttpControllerCliIntegrationTest extends TestCase
{
    public function testGuardReturnsCanonicalAllowedDecision(): void
    {
        $request = $this->request('integration-user');

        $decision = (new AdvancedAuthorizationGuard(
            $this->authorization()
        ))->decide($request);

        self::assertTrue($decision->isAllowed());
    }

    public function testGuardReturnsCanonicalDeniedDecisionForDifferentResource(): void
    {
        $request = $this->request('other-user');

        $decision = (new AdvancedAuthorizationGuard(
            $this->authorization()
        ))->decide($request);

        self::assertFalse($decision->isAllowed());
    }

    public function testControllerBridgeDoesNotChooseHttpResponseStrategy(): void
    {
        $bridge = new ControllerAuthorizationBridge(
            new AdvancedAuthorizationGuard($this->authorization())
        );

        $decision = $bridge->authorize(
            $this->request('integration-user')
        );

        self::assertTrue($decision->isAllowed());

        $source = file_get_contents(
            dirname(__DIR__, 4)
            . '/src/Foundation/Security/Authorization/Integration/'
            . 'ControllerAuthorizationBridge.php'
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('Response', $source);
        self::assertStringNotContainsString('403', $source);
        self::assertStringNotContainsString('404', $source);
        self::assertStringNotContainsString('redirect', strtolower($source));
    }

    public function testCliInspectionUsesSanitizedDiagnosticSnapshot(): void
    {
        $request = $this->request('integration-user');

        $command = new AdvancedAuthorizationInspectCommand(
            $this->diagnostics(),
            $request
        );

        $result = $command->execute(
            new CliInvocation(
                new CliCommandName('security:authorization:inspect'),
                []
            )
        );

        self::assertTrue($result->exitCode()->successful());

        $encoded = json_encode($result->data(), JSON_THROW_ON_ERROR);

        self::assertStringContainsString('identity_fingerprint', $encoded);
        self::assertStringContainsString('evaluation_fingerprint', $encoded);
        self::assertStringNotContainsString('integration-user', $encoded);
    }

    public function testAuthorizationIntegrationDoesNotMutateAuthenticationState(): void
    {
        $principal = $this->principal();

        $request = new AdvancedAuthorizationRequest(
            $principal,
            new AuthorizationAttributeBag([
                'owner.id' => 'integration-user',
            ])
        );

        self::assertTrue(
            (new AdvancedAuthorizationGuard(
                $this->authorization()
            ))->isAllowed($request)
        );

        self::assertSame(50, $principal->evidence()->level()->value());
    }

    public function testApplicationIntegrationRemainsOptInWithoutGlobalRegistration(): void
    {
        foreach ([
            'AdvancedAuthorizationGuard.php',
            'ControllerAuthorizationBridge.php',
        ] as $file) {
            $source = file_get_contents(
                dirname(__DIR__, 4)
                . '/src/Foundation/Security/Authorization/Integration/'
                . $file
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('Route', $source);
            self::assertStringNotContainsString('ServiceProvider', $source);
            self::assertStringNotContainsString('register(', $source);
            self::assertStringNotContainsString('boot(', $source);
        }
    }

    private function request(string $owner): AdvancedAuthorizationRequest
    {
        return new AdvancedAuthorizationRequest(
            $this->principal(),
            new AuthorizationAttributeBag([
                'owner.id' => $owner,
                'classification' => 'internal',
            ]),
            new AuthorizationAttributeBag([
                'maintenance' => false,
            ])
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
                    AuthorizationAttributeScope::Subject,
                    'department',
                    AuthorizationAttributeComparison::Equals,
                    'finance'
                ),
                new AttributeRequirement(
                    AuthorizationAttributeScope::Resource,
                    'owner.id',
                    AuthorizationAttributeComparison::Equals,
                    'integration-user'
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
            new EffectivePermissionResolver(
                new RoleHierarchy([
                    new RoleDefinition(
                        new RoleIdentifier('manager'),
                        new PermissionSet([
                            new PermissionIdentifier('invoice.approve'),
                        ])
                    ),
                ])
            )
        );
    }

    private function principal(): AuthenticatedPrincipal
    {
        return new AuthenticatedPrincipal(
            new Identity(new IdentityId('integration-user')),
            new PrincipalAttributeCollection(),
            new AuthenticationEvidence(
                new AuthenticationMethod('password'),
                new AuthenticationLevel(50),
                new \DateTimeImmutable('2026-08-07T18:00:00+00:00')
            )
        );
    }
}
