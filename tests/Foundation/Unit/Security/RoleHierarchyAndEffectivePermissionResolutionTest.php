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

final class RoleHierarchyAndEffectivePermissionResolutionTest extends TestCase
{
    public function testRoleHierarchyExpandsInheritedRolesDeterministically(): void
    {
        $hierarchy = $this->hierarchy();

        $expanded = $hierarchy->expand(
            new RoleIdentifier('administrator')
        );

        self::assertSame(
            ['administrator', 'auditor', 'manager'],
            array_map(
                static fn (RoleIdentifier $role): string => $role->value(),
                $expanded
            )
        );
    }

    public function testEffectivePermissionsIncludeInheritedAndDirectPermissions(): void
    {
        $resolver = new EffectivePermissionResolver($this->hierarchy());

        $permissions = $resolver->resolve(
            new RoleSet([new RoleIdentifier('administrator')]),
            new PermissionSet([
                new PermissionIdentifier('profile.self.edit'),
            ])
        );

        self::assertSame(
            [
                'audit.read',
                'invoice.approve',
                'invoice.read',
                'profile.self.edit',
                'system.configure',
            ],
            $permissions->values()
        );
    }

    public function testUnknownRolesDoNotGrantImplicitPermissions(): void
    {
        $resolver = new EffectivePermissionResolver($this->hierarchy());

        $permissions = $resolver->resolve(
            new RoleSet([new RoleIdentifier('unknown-role')])
        );

        self::assertSame([], $permissions->values());
    }

    public function testCycleDetectionFailsFast(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new RoleHierarchy([
            new RoleDefinition(
                new RoleIdentifier('role-a'),
                new PermissionSet(),
                new RoleSet([new RoleIdentifier('role-b')])
            ),
            new RoleDefinition(
                new RoleIdentifier('role-b'),
                new PermissionSet(),
                new RoleSet([new RoleIdentifier('role-a')])
            ),
        ]);
    }

    public function testPrincipalGrantResolverCombinesExternalResolversWithoutDecidingAuthorization(): void
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
                return new PermissionSet([
                    new PermissionIdentifier('profile.self.edit'),
                ]);
            }
        };

        $resolver = new PrincipalAuthorizationGrantResolver(
            $roles,
            $permissions,
            new EffectivePermissionResolver($this->hierarchy())
        );

        $resolved = $resolver->resolve($this->principal());

        self::assertSame(['manager'], $resolved->roles()->values());
        self::assertSame(
            [
                'audit.read',
                'invoice.approve',
                'invoice.read',
                'profile.self.edit',
            ],
            $resolved->permissions()->values()
        );

        $source = file_get_contents(
            dirname(__DIR__, 4)
            . '/src/Foundation/Security/Authorization/Permission/'
            . 'PrincipalAuthorizationGrantResolver.php'
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('AuthorizationDecision', $source);
        self::assertStringNotContainsString('authorize(', $source);
        self::assertStringNotContainsString('allow(', $source);
        self::assertStringNotContainsString('deny(', $source);
    }

    public function testDuplicateRoleDefinitionsAreRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new RoleHierarchy([
            new RoleDefinition(
                new RoleIdentifier('manager'),
                new PermissionSet()
            ),
            new RoleDefinition(
                new RoleIdentifier('MANAGER'),
                new PermissionSet()
            ),
        ]);
    }

    private function hierarchy(): RoleHierarchy
    {
        return new RoleHierarchy([
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
            new RoleDefinition(
                new RoleIdentifier('administrator'),
                new PermissionSet([
                    new PermissionIdentifier('system.configure'),
                ]),
                new RoleSet([
                    new RoleIdentifier('manager'),
                ])
            ),
        ]);
    }

    private function principal(): AuthenticatedPrincipal
    {
        return new AuthenticatedPrincipal(
            new Identity(new IdentityId('authorization-user')),
            new PrincipalAttributeCollection(),
            new AuthenticationEvidence(
                new AuthenticationMethod('password'),
                new AuthenticationLevel(50),
                new \DateTimeImmutable('2026-08-07T13:00:00+00:00')
            )
        );
    }
}
