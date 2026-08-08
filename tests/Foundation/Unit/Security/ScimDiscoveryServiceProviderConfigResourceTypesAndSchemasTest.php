<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\ScimDiscoveryProviderInterface;
use Sif\Foundation\Security\Scim\ScimFeatureSupport;
use Sif\Foundation\Security\Scim\ScimResourceType;
use Sif\Foundation\Security\Scim\ScimSchemaAttribute;
use Sif\Foundation\Security\Scim\ScimSchemaDefinition;
use Sif\Foundation\Security\Scim\ScimSchemaUri;
use Sif\Foundation\Security\Scim\ScimServiceProviderConfig;

final class ScimDiscoveryServiceProviderConfigResourceTypesAndSchemasTest extends TestCase
{
    public function testServiceProviderConfigRepresentsSupportedCapabilities(): void
    {
        $config = new ScimServiceProviderConfig(
            new ScimFeatureSupport(true),
            new ScimFeatureSupport(true, 1000, 1048576),
            new ScimFeatureSupport(true),
            new ScimFeatureSupport(false),
            new ScimFeatureSupport(true),
            new ScimFeatureSupport(true)
        );

        self::assertTrue($config->patch()->supported());
        self::assertTrue($config->bulk()->supported());
        self::assertSame(1000, $config->bulk()->maxOperations());
        self::assertFalse($config->changePassword()->supported());
        self::assertTrue($config->etag()->supported());
    }

    public function testResourceTypeDeclaresEndpointAndSchema(): void
    {
        $resourceType = new ScimResourceType(
            'User',
            'User',
            '/Users',
            new ScimSchemaUri(
                'urn:ietf:params:scim:schemas:core:2.0:User'
            )
        );

        self::assertSame('User', $resourceType->id());
        self::assertSame('/Users', $resourceType->endpoint());
        self::assertSame(
            'urn:ietf:params:scim:schemas:core:2.0:User',
            $resourceType->schema()->value()
        );
    }

    public function testSchemaDefinitionRepresentsAttributeMetadata(): void
    {
        $schema = new ScimSchemaDefinition(
            new ScimSchemaUri(
                'urn:ietf:params:scim:schemas:core:2.0:User'
            ),
            'User',
            'Core User schema',
            [
                new ScimSchemaAttribute(
                    'userName',
                    'string',
                    required: true,
                    mutability: 'readWrite',
                    returned: 'default',
                    uniqueness: 'server'
                ),
            ]
        );

        self::assertSame('User', $schema->name());
        self::assertCount(1, $schema->attributes());
        self::assertSame(
            'userName',
            $schema->attributes()[0]->name()
        );
        self::assertTrue(
            $schema->attributes()[0]->required()
        );
    }

    public function testSchemaAttributeSupportsComplexSubAttributes(): void
    {
        $attribute = new ScimSchemaAttribute(
            'name',
            'complex',
            subAttributes: [
                new ScimSchemaAttribute(
                    'givenName',
                    'string'
                ),
                new ScimSchemaAttribute(
                    'familyName',
                    'string'
                ),
            ]
        );

        self::assertSame('complex', $attribute->type());
        self::assertCount(2, $attribute->subAttributes());
        self::assertSame(
            'givenName',
            $attribute->subAttributes()[0]->name()
        );
    }

    public function testDiscoveryProviderContractRemainsTransportNeutral(): void
    {
        $reflection = new \ReflectionClass(
            ScimDiscoveryProviderInterface::class
        );
        $source = file_get_contents(
            (string) $reflection->getFileName()
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('curl_', strtolower($source));
        self::assertStringNotContainsString('PDO', $source);
        self::assertStringNotContainsString('Redis', $source);
        self::assertStringNotContainsString('Keycloak', $source);
    }

    public function testDiscoveryModelsDoNotExecuteProtocolOperations(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/Scim';

        foreach (glob($directory . '/*.php') ?: [] as $file) {
            $source = file_get_contents($file);

            self::assertIsString($source);
            self::assertStringNotContainsString(
                'http_response_code',
                strtolower($source)
            );
            self::assertStringNotContainsString(
                'header(',
                strtolower($source)
            );
        }
    }
}
