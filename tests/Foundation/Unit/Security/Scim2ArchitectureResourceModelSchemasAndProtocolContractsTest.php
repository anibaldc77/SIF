<?php
declare(strict_types=1);
namespace Sif\Tests\Foundation\Unit\Security;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\ScimGroupProvisionerInterface;
use Sif\Foundation\Security\Contracts\ScimUserProvisionerInterface;
use Sif\Foundation\Security\Scim\ScimGroup;
use Sif\Foundation\Security\Scim\ScimGroupMember;
use Sif\Foundation\Security\Scim\ScimMeta;
use Sif\Foundation\Security\Scim\ScimResourceId;
use Sif\Foundation\Security\Scim\ScimSchemaUri;
use Sif\Foundation\Security\Scim\ScimUser;

final class Scim2ArchitectureResourceModelSchemasAndProtocolContractsTest extends TestCase
{
    public function testUserResourceCarriesSchemasUsernameAndActiveState(): void
    {
        $user = new ScimUser(
            [new ScimSchemaUri('urn:ietf:params:scim:schemas:core:2.0:User')],
            'alice@example.com',
            true
        );
        self::assertSame('alice@example.com', $user->userName());
        self::assertTrue($user->active());
        self::assertSame('urn:ietf:params:scim:schemas:core:2.0:User', $user->schemas()[0]->value());
    }

    public function testGroupResourceCarriesMembers(): void
    {
        $group = new ScimGroup(
            [new ScimSchemaUri('urn:ietf:params:scim:schemas:core:2.0:Group')],
            'Administrators',
            [new ScimGroupMember(new ScimResourceId('user-123'), 'Alice')]
        );
        self::assertSame('Administrators', $group->displayName());
        self::assertCount(1, $group->members());
        self::assertSame('user-123', $group->members()[0]->value()->value());
    }

    public function testMetaIsStorageNeutralProtocolMetadata(): void
    {
        $meta = new ScimMeta('User', version: 'W/"12345"', location: '/scim/v2/Users/user-123');
        self::assertSame('User', $meta->resourceType());
        self::assertSame('W/"12345"', $meta->version());
        self::assertSame('/scim/v2/Users/user-123', $meta->location());
    }

    public function testProvisioningContractsRemainPersistenceNeutral(): void
    {
        foreach ([ScimUserProvisionerInterface::class, ScimGroupProvisionerInterface::class] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());
            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('SQL', strtoupper($source));
            self::assertStringNotContainsString('Redis', $source);
        }
    }

    public function testFoundationDoesNotContainVendorSpecificScimDependencies(): void
    {
        $directory = dirname(__DIR__, 4) . '/src/Foundation/Security/Scim';
        foreach (glob($directory . '/*.php') ?: [] as $file) {
            $source = file_get_contents($file);
            self::assertIsString($source);
            self::assertStringNotContainsString('Keycloak', $source);
            self::assertStringNotContainsString('Microsoft', $source);
            self::assertStringNotContainsString('Okta', $source);
            self::assertStringNotContainsString('OneLogin', $source);
        }
    }

    public function testResourceModelsDoNotPerformTransport(): void
    {
        $directory = dirname(__DIR__, 4) . '/src/Foundation/Security/Scim';
        foreach (glob($directory . '/*.php') ?: [] as $file) {
            $source = file_get_contents($file);
            self::assertIsString($source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('file_get_contents("http', strtolower($source));
        }
    }
}
