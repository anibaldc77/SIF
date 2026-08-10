<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OAuthAuthorizationServerMetadataSerializerInterface;
use Sif\Foundation\Security\Contracts\OAuthAuthorizationServerMetadataValidatorInterface;
use Sif\Foundation\Security\OAuth\Metadata\OAuthAuthorizationServerMetadata;
use Sif\Foundation\Security\OAuth\Metadata\OAuthAuthorizationServerMetadataBuilder;

final class OAuthAuthorizationServerMetadataModelBuilderAndContractsTest extends TestCase
{
    public function testMetadataRepresentsCoreAndAdvancedEndpoints(): void
    {
        $metadata = new OAuthAuthorizationServerMetadata(
            'https://issuer.example.test',
            'https://issuer.example.test/authorize',
            'https://issuer.example.test/token',
            ['authorization_code', 'client_credentials'],
            ['code'],
            ['profile', 'orders.read'],
            ['client_secret', 'private_key_jwt'],
            ['S256'],
            'https://issuer.example.test/jwks',
            'https://issuer.example.test/register',
            'https://issuer.example.test/revoke',
            'https://issuer.example.test/introspect',
            'https://issuer.example.test/par'
        );

        self::assertSame(
            'https://issuer.example.test',
            $metadata->issuer()
        );
        self::assertSame(
            ['authorization_code', 'client_credentials'],
            $metadata->grantTypesSupported()
        );
        self::assertSame(
            ['client_secret', 'private_key_jwt'],
            $metadata->tokenEndpointAuthMethodsSupported()
        );
        self::assertSame(
            ['S256'],
            $metadata->codeChallengeMethodsSupported()
        );
        self::assertSame(
            'https://issuer.example.test/par',
            $metadata->pushedAuthorizationRequestEndpoint()
        );
    }

    public function testBuilderProducesEquivalentTypedMetadata(): void
    {
        $metadata = (new OAuthAuthorizationServerMetadataBuilder(
            'https://issuer.example.test',
            'https://issuer.example.test/authorize',
            'https://issuer.example.test/token'
        ))
            ->withGrantTypes(['authorization_code'])
            ->withResponseTypes(['code'])
            ->withScopes(['profile'])
            ->withTokenEndpointAuthMethods(['private_key_jwt'])
            ->withCodeChallengeMethods(['S256'])
            ->withJwksUri('https://issuer.example.test/jwks')
            ->withRevocationEndpoint('https://issuer.example.test/revoke')
            ->withIntrospectionEndpoint('https://issuer.example.test/introspect')
            ->withPushedAuthorizationRequestEndpoint('https://issuer.example.test/par')
            ->build();

        self::assertSame(
            ['authorization_code'],
            $metadata->grantTypesSupported()
        );
        self::assertSame(
            'https://issuer.example.test/revoke',
            $metadata->revocationEndpoint()
        );
        self::assertSame(
            'https://issuer.example.test/introspect',
            $metadata->introspectionEndpoint()
        );
    }

    public function testBuilderIsPersistentRatherThanMutatingPreviousState(): void
    {
        $builder = new OAuthAuthorizationServerMetadataBuilder(
            'https://issuer.example.test',
            'https://issuer.example.test/authorize',
            'https://issuer.example.test/token'
        );

        $withScopes = $builder->withScopes(['profile']);

        self::assertSame([], $builder->build()->scopesSupported());
        self::assertSame(
            ['profile'],
            $withScopes->build()->scopesSupported()
        );
    }

    public function testSerializerContractReturnsStructuredRepresentation(): void
    {
        $method = new \ReflectionMethod(
            OAuthAuthorizationServerMetadataSerializerInterface::class,
            'serialize'
        );

        self::assertSame('array', (string) $method->getReturnType());
    }

    public function testMetadataValidationRemainsBehindContract(): void
    {
        $reflection = new \ReflectionClass(
            OAuthAuthorizationServerMetadataValidatorInterface::class
        );

        self::assertTrue($reflection->isInterface());
        self::assertTrue($reflection->hasMethod('validate'));
    }

    public function testMetadataLayerRemainsTransportAndInfrastructureNeutral(): void
    {
        foreach ([
            OAuthAuthorizationServerMetadata::class,
            OAuthAuthorizationServerMetadataBuilder::class,
            OAuthAuthorizationServerMetadataSerializerInterface::class,
            OAuthAuthorizationServerMetadataValidatorInterface::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString(
                'http_response_code',
                strtolower($source)
            );
        }
    }
}
