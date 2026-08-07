<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Authorization\Attribute\AuthorizationAttributeBag;
use Sif\Foundation\Security\Authorization\Permission\PermissionIdentifier;
use Sif\Foundation\Security\Authorization\Permission\PermissionSet;
use Sif\Foundation\Security\Authorization\Role\RoleIdentifier;
use Sif\Foundation\Security\Authorization\Role\RoleSet;
use Sif\Foundation\Security\Contracts\AuthorizationAttributeProviderInterface;
use Sif\Foundation\Security\Contracts\PermissionResolverInterface;
use Sif\Foundation\Security\Contracts\RoleResolverInterface;

final class AdvancedAuthorizationPermissionsRolesAttributesArchitectureTest extends TestCase
{
    public function testPermissionIdentifiersAreCanonicalAndSetsAreDeterministic(): void
    {
        $set = new PermissionSet([
            new PermissionIdentifier('Invoices.Read'),
            new PermissionIdentifier('invoices.write'),
            new PermissionIdentifier('invoices.read'),
        ]);

        self::assertSame(2, $set->count());
        self::assertSame(
            ['invoices.read', 'invoices.write'],
            $set->values()
        );
        self::assertTrue(
            $set->contains(new PermissionIdentifier('INVOICES.READ'))
        );
    }

    public function testRoleIdentifiersAreCanonicalAndSetsAreDeterministic(): void
    {
        $set = new RoleSet([
            new RoleIdentifier('Manager'),
            new RoleIdentifier('Auditor'),
            new RoleIdentifier('manager'),
        ]);

        self::assertSame(2, $set->count());
        self::assertSame(
            ['auditor', 'manager'],
            $set->values()
        );
    }

    public function testAuthorizationAttributesAreNormalizedWithoutBecomingPermissions(): void
    {
        $bag = new AuthorizationAttributeBag([
            'Department' => 'finance',
            'tenant.id' => 42,
            'resource.owner' => true,
        ]);

        self::assertTrue($bag->has('department'));
        self::assertSame('finance', $bag->get('DEPARTMENT'));
        self::assertSame(42, $bag->get('tenant.id'));
        self::assertSame(true, $bag->get('resource.owner'));
    }

    public function testResolverContractsRemainPersistenceAndTransportNeutral(): void
    {
        foreach ([
            PermissionResolverInterface::class,
            RoleResolverInterface::class,
            AuthorizationAttributeProviderInterface::class,
        ] as $contract) {
            $reflection = new \ReflectionClass($contract);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('BaseModel', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('Cookie', $source);
            self::assertStringNotContainsString('Session', $source);
            self::assertStringNotContainsString('Keycloak', $source);
        }
    }

    public function testNewAuthorizationVocabularyDoesNotDefineASecondDecisionEngine(): void
    {
        $files = [
            dirname(__DIR__, 4)
            . '/src/Foundation/Security/Authorization/Permission/PermissionSet.php',
            dirname(__DIR__, 4)
            . '/src/Foundation/Security/Authorization/Role/RoleSet.php',
            dirname(__DIR__, 4)
            . '/src/Foundation/Security/Authorization/Attribute/AuthorizationAttributeBag.php',
        ];

        foreach ($files as $file) {
            $source = file_get_contents($file);

            self::assertIsString($source);
            self::assertStringNotContainsString('AuthorizationDecision', $source);
            self::assertStringNotContainsString('authorize(', $source);
            self::assertStringNotContainsString('isGranted(', $source);
            self::assertStringNotContainsString('deny(', $source);
            self::assertStringNotContainsString('allow(', $source);
        }
    }

    public function testPermissionAndRoleCollectionsAreImmutableValueObjects(): void
    {
        self::assertTrue(
            (new \ReflectionClass(PermissionSet::class))->isReadOnly()
        );
        self::assertTrue(
            (new \ReflectionClass(RoleSet::class))->isReadOnly()
        );
        self::assertTrue(
            (new \ReflectionClass(AuthorizationAttributeBag::class))->isReadOnly()
        );
    }
}
