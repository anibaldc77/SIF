<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OAuthProtectedResourceMetadataSerializerInterface;
use Sif\Foundation\Security\Contracts\OAuthProtectedResourceMetadataUriResolverInterface;
use Sif\Foundation\Security\Contracts\OAuthProtectedResourceMetadataValidatorInterface;
use Sif\Foundation\Security\Contracts\OAuthSignedProtectedResourceMetadataVerifierInterface;
use Sif\Foundation\Security\OAuth\Metadata\OAuthProtectedResourceMetadata;
use Sif\Foundation\Security\OAuth\Metadata\OAuthProtectedResourceMetadataBuilder;

final class OAuthProtectedResourceMetadataModelBuilderAndContractsTest extends TestCase
{
    public function testMetadataRepresentsRfc9728SecurityCapabilities(): void
    {
        $metadata = new OAuthProtectedResourceMetadata(
            'https://api.example.test/orders',
            ['https://issuer.example.test'],
            ['orders.read', 'orders.write'],
            'https://api.example.test/jwks',
            ['header'],
            ['ES256'],
            'Orders API',
            'https://api.example.test/docs',
            'https://api.example.test/policy',
            'https://api.example.test/terms',
            true,
            ['order_access'],
            ['ES256'],
            true,
            'header.payload.signature'
        );

        self::assertSame(
            'https://api.example.test/orders',
            $metadata->resource()
        );
        self::assertSame(
            ['https://issuer.example.test'],
            $metadata->authorizationServers()
        );
        self::assertSame(['header'], $metadata->bearerMethodsSupported());
        self::assertTrue(
            $metadata->tlsClientCertificateBoundAccessTokens()
        );
        self::assertSame(
            ['order_access'],
            $metadata->authorizationDetailsTypesSupported()
        );
        self::assertSame(
            ['ES256'],
            $metadata->dpopSigningAlgValuesSupported()
        );
        self::assertTrue($metadata->dpopBoundAccessTokensRequired());
    }

    public function testBuilderProducesTypedMetadataWithoutMutation(): void
    {
        $builder = new OAuthProtectedResourceMetadataBuilder(
            'https://api.example.test',
            ['https://issuer.example.test']
        );

        $secured = $builder
            ->withScopes(['orders.read'])
            ->withBearerMethods(['header'])
            ->withAuthorizationDetailsTypes(['order_access'])
            ->withDpopSigningAlgorithms(['ES256'])
            ->withDpopBoundAccessTokensRequired(true);

        self::assertSame([], $builder->build()->scopesSupported());
        self::assertSame(
            ['orders.read'],
            $secured->build()->scopesSupported()
        );
        self::assertTrue(
            $secured->build()->dpopBoundAccessTokensRequired()
        );
    }

    public function testExistingI1ConstructorShapeRemainsCompatible(): void
    {
        $metadata = new OAuthProtectedResourceMetadata(
            'https://api.example.test',
            ['https://issuer.example.test'],
            ['profile']
        );

        self::assertSame(['profile'], $metadata->scopesSupported());
        self::assertNull($metadata->jwksUri());
        self::assertFalse($metadata->dpopBoundAccessTokensRequired());
    }

    public function testValidatorReceivesExpectedResourceForExactMatching(): void
    {
        $method = new \ReflectionMethod(
            OAuthProtectedResourceMetadataValidatorInterface::class,
            'validate'
        );

        self::assertCount(2, $method->getParameters());
        self::assertSame(
            'string',
            (string) $method->getParameters()[1]->getType()
        );
    }

    public function testMetadataContractsAreTyped(): void
    {
        $serializer = new \ReflectionMethod(
            OAuthProtectedResourceMetadataSerializerInterface::class,
            'serialize'
        );
        $resolver = new \ReflectionMethod(
            OAuthProtectedResourceMetadataUriResolverInterface::class,
            'resolve'
        );
        $verifier = new \ReflectionMethod(
            OAuthSignedProtectedResourceMetadataVerifierInterface::class,
            'verify'
        );

        self::assertSame('array', (string) $serializer->getReturnType());
        self::assertSame('string', (string) $resolver->getReturnType());
        self::assertSame(
            OAuthProtectedResourceMetadata::class,
            (string) $verifier->getReturnType()
        );
    }

    public function testProtectedResourceMetadataLayerRemainsInfrastructureNeutral(): void
    {
        foreach ([
            OAuthProtectedResourceMetadata::class,
            OAuthProtectedResourceMetadataBuilder::class,
            OAuthProtectedResourceMetadataSerializerInterface::class,
            OAuthProtectedResourceMetadataValidatorInterface::class,
            OAuthProtectedResourceMetadataUriResolverInterface::class,
            OAuthSignedProtectedResourceMetadataVerifierInterface::class,
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
