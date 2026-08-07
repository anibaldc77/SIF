<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OidcNonceGeneratorInterface;
use Sif\Foundation\Security\Contracts\OidcProviderMetadataProviderInterface;
use Sif\Foundation\Security\Contracts\OidcStateGeneratorInterface;
use Sif\Foundation\Security\Oidc\OidcClientRegistration;
use Sif\Foundation\Security\Oidc\OidcNonce;
use Sif\Foundation\Security\Oidc\OidcProviderMetadata;
use Sif\Foundation\Security\Oidc\OidcState;

final class OpenIdConnectClientArchitectureAndProviderMetadataContractsTest extends TestCase
{
    public function testProviderMetadataCapturesRequiredDiscoveryValues(): void
    {
        $metadata = $this->metadata();

        self::assertSame(
            'https://identity.example',
            $metadata->issuer()
        );
        self::assertSame(
            'https://identity.example/authorize',
            $metadata->authorizationEndpoint()
        );
        self::assertSame(
            'https://identity.example/token',
            $metadata->tokenEndpoint()
        );
        self::assertSame(
            'https://identity.example/jwks',
            $metadata->jwksUri()
        );
        self::assertSame(
            ['RS256'],
            $metadata->idTokenSigningAlgorithmsSupported()
        );
    }

    public function testClientRegistrationContainsNoClientSecretMaterial(): void
    {
        $registration = new OidcClientRegistration(
            'sif-web-client',
            'https://app.example/login/oidc/callback',
            true
        );

        self::assertSame(
            'sif-web-client',
            $registration->clientId()
        );
        self::assertTrue($registration->isConfidential());

        $reflection = new \ReflectionClass($registration);
        self::assertFalse(
            $reflection->hasProperty('clientSecret')
        );
    }

    public function testStateAndNonceAreStronglyTypedAndUrlSafe(): void
    {
        $state = new OidcState(
            'abcdefghijklmnopqrstuvwxyz0123456789_STATE'
        );
        $nonce = new OidcNonce(
            'abcdefghijklmnopqrstuvwxyz0123456789_NONCE'
        );

        self::assertStringContainsString(
            'STATE',
            $state->value()
        );
        self::assertStringContainsString(
            'NONCE',
            $nonce->value()
        );
    }

    public function testProviderMetadataContractRemainsTransportNeutral(): void
    {
        $reflection = new \ReflectionClass(
            OidcProviderMetadataProviderInterface::class
        );

        $source = file_get_contents(
            (string) $reflection->getFileName()
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('curl', strtolower($source));
        self::assertStringNotContainsString('HttpClient', $source);
        self::assertStringNotContainsString('Keycloak', $source);
        self::assertStringNotContainsString('file_get_contents', $source);
    }

    public function testGeneratorsAreContractsWithoutRandomnessPolicyLeakage(): void
    {
        foreach ([
            OidcStateGeneratorInterface::class,
            OidcNonceGeneratorInterface::class,
        ] as $contract) {
            $reflection = new \ReflectionClass($contract);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('random_bytes', $source);
            self::assertStringNotContainsString('openssl_', $source);
        }
    }

    public function testOidcFoundationReusesOAuthAndJwtLayersInsteadOfDuplicatingThem(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/Oidc';

        foreach (glob($directory . '/*.php') ?: [] as $file) {
            $source = file_get_contents($file);

            self::assertIsString($source);
            self::assertStringNotContainsString(
                'class AccessToken',
                $source
            );
            self::assertStringNotContainsString(
                'class Jwk',
                $source
            );
            self::assertStringNotContainsString(
                'class JwtHeader',
                $source
            );
        }
    }

    private function metadata(): OidcProviderMetadata
    {
        return new OidcProviderMetadata(
            'https://identity.example',
            'https://identity.example/authorize',
            'https://identity.example/token',
            'https://identity.example/jwks',
            ['code'],
            ['public'],
            ['RS256'],
            'https://identity.example/userinfo'
        );
    }
}
