<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OAuthAuthorizationServerMetadataProviderInterface;
use Sif\Foundation\Security\Contracts\OAuthClientRegistrationMetadataValidatorInterface;
use Sif\Foundation\Security\Contracts\OAuthDynamicClientRegistrationServiceInterface;
use Sif\Foundation\Security\Contracts\OAuthMetadataResolverInterface;
use Sif\Foundation\Security\Contracts\OAuthProtectedResourceMetadataProviderInterface;
use Sif\Foundation\Security\OAuth\Metadata\OAuthAuthorizationServerMetadata;
use Sif\Foundation\Security\OAuth\Metadata\OAuthClientRegistrationMetadata;
use Sif\Foundation\Security\OAuth\Metadata\OAuthProtectedResourceMetadata;

final class OAuthMetadataDiscoveryAndDynamicClientLifecycleArchitectureTest extends TestCase
{
    public function testAuthorizationServerMetadataKeepsIssuerAndEndpointsExplicit(): void
    {
        $metadata = new OAuthAuthorizationServerMetadata(
            'https://issuer.example.test',
            'https://issuer.example.test/authorize',
            'https://issuer.example.test/token',
            ['authorization_code'],
            ['code'],
            ['profile'],
            [],
            [],
            'https://issuer.example.test/jwks',
            'https://issuer.example.test/register'
        );

        self::assertSame(
            'https://issuer.example.test',
            $metadata->issuer()
        );
        self::assertSame(
            'https://issuer.example.test/token',
            $metadata->tokenEndpoint()
        );
        self::assertSame(
            'https://issuer.example.test/register',
            $metadata->registrationEndpoint()
        );
    }

    public function testProtectedResourceMetadataKeepsAuthorizationServerRelationshipExplicit(): void
    {
        $metadata = new OAuthProtectedResourceMetadata(
            'https://api.example.test',
            ['https://issuer.example.test'],
            ['orders.read']
        );

        self::assertSame(
            'https://api.example.test',
            $metadata->resource()
        );
        self::assertSame(
            ['https://issuer.example.test'],
            $metadata->authorizationServers()
        );
    }

    public function testClientRegistrationMetadataIsSeparateFromStoredClient(): void
    {
        $metadata = new OAuthClientRegistrationMetadata(
            'Example Client',
            ['https://client.example.test/callback'],
            ['authorization_code'],
            ['code']
        );

        self::assertSame('Example Client', $metadata->clientName());
        self::assertCount(1, $metadata->redirectUris());
    }

    public function testMetadataAndRegistrationContractsAreTyped(): void
    {
        $authorizationServer = new \ReflectionMethod(
            OAuthAuthorizationServerMetadataProviderInterface::class,
            'metadata'
        );
        $protectedResource = new \ReflectionMethod(
            OAuthProtectedResourceMetadataProviderInterface::class,
            'metadataFor'
        );
        $registration = new \ReflectionMethod(
            OAuthDynamicClientRegistrationServiceInterface::class,
            'register'
        );

        self::assertSame(
            OAuthAuthorizationServerMetadata::class,
            (string) $authorizationServer->getReturnType()
        );
        self::assertSame(
            OAuthProtectedResourceMetadata::class,
            (string) $protectedResource->getReturnType()
        );
        self::assertSame(
            \Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClient::class,
            (string) $registration->getReturnType()
        );
    }

    public function testDiscoveryAndRegistrationBoundariesRemainInfrastructureNeutral(): void
    {
        foreach ([
            OAuthAuthorizationServerMetadataProviderInterface::class,
            OAuthProtectedResourceMetadataProviderInterface::class,
            OAuthDynamicClientRegistrationServiceInterface::class,
            OAuthClientRegistrationMetadataValidatorInterface::class,
            OAuthMetadataResolverInterface::class,
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

    public function testOauthMetadataLayerDoesNotDuplicateOidcProviderMetadata(): void
    {
        foreach ([
            OAuthAuthorizationServerMetadata::class,
            OAuthProtectedResourceMetadata::class,
            OAuthClientRegistrationMetadata::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString(
                'OidcProviderMetadata',
                $source
            );
        }
    }
}
