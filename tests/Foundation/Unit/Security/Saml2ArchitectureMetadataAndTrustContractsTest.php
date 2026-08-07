<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\SamlIdentityProviderMetadataProviderInterface;
use Sif\Foundation\Security\Contracts\SamlTrustStoreInterface;
use Sif\Foundation\Security\Saml\SamlCertificateFingerprint;
use Sif\Foundation\Security\Saml\SamlEndpoint;
use Sif\Foundation\Security\Saml\SamlEntityId;
use Sif\Foundation\Security\Saml\SamlIdentityProviderMetadata;
use Sif\Foundation\Security\Saml\SamlServiceProviderMetadata;

final class Saml2ArchitectureMetadataAndTrustContractsTest extends TestCase
{
    public function testIdentityProviderMetadataRequiresExplicitSsoEndpoint(): void
    {
        $metadata = new SamlIdentityProviderMetadata(
            new SamlEntityId('https://idp.example/saml'),
            [
                new SamlEndpoint(
                    'https://idp.example/saml/sso',
                    'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect'
                ),
            ]
        );

        self::assertSame(
            'https://idp.example/saml',
            $metadata->entityId()->value()
        );
        self::assertCount(
            1,
            $metadata->singleSignOnServices()
        );
    }

    public function testServiceProviderMetadataSeparatesAcsAndLogoutEndpoints(): void
    {
        $metadata = new SamlServiceProviderMetadata(
            new SamlEntityId('https://app.example/saml'),
            new SamlEndpoint(
                'https://app.example/saml/acs',
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST'
            ),
            new SamlEndpoint(
                'https://app.example/saml/logout',
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect'
            )
        );

        self::assertSame(
            'https://app.example/saml/acs',
            $metadata->assertionConsumerService()->location()
        );
        self::assertSame(
            'https://app.example/saml/logout',
            $metadata->singleLogoutService()?->location()
        );
    }

    public function testCertificateFingerprintIsStronglyTyped(): void
    {
        $fingerprint = new SamlCertificateFingerprint(
            str_repeat('ab', 32)
        );

        self::assertSame(
            str_repeat('ab', 32),
            $fingerprint->sha256()
        );
    }

    public function testMetadataProviderContractRemainsTransportNeutral(): void
    {
        $reflection = new \ReflectionClass(
            SamlIdentityProviderMetadataProviderInterface::class
        );

        $source = file_get_contents(
            (string) $reflection->getFileName()
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('curl_', strtolower($source));
        self::assertStringNotContainsString('file_get_contents(', strtolower($source));
        self::assertStringNotContainsString('http://', strtolower($source));
        self::assertStringNotContainsString('https://', strtolower($source));
    }

    public function testTrustStoreContractRemainsStorageNeutral(): void
    {
        $reflection = new \ReflectionClass(
            SamlTrustStoreInterface::class
        );

        $source = file_get_contents(
            (string) $reflection->getFileName()
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('PDO', $source);
        self::assertStringNotContainsString('SQL', strtoupper($source));
        self::assertStringNotContainsString('Redis', $source);
    }

    public function testSamlFoundationDoesNotContainProviderSpecificDependencies(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/Saml';

        foreach (glob($directory . '/*.php') ?: [] as $file) {
            $source = file_get_contents($file);

            self::assertIsString($source);
            self::assertStringNotContainsString('Keycloak', $source);
            self::assertStringNotContainsString('Microsoft', $source);
            self::assertStringNotContainsString('Okta', $source);
            self::assertStringNotContainsString('OneLogin', $source);
            self::assertStringNotContainsString('Shibboleth', $source);
        }
    }
}
