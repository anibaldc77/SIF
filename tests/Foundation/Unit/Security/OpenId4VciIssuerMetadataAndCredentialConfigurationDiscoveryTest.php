<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\CredentialConfigurationResolverInterface;
use Sif\Foundation\Security\Contracts\CredentialIssuerMetadataProviderInterface;
use Sif\Foundation\Security\Contracts\CredentialIssuerMetadataResolverInterface;
use Sif\Foundation\Security\Contracts\CredentialIssuerMetadataValidatorInterface;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialConfiguration;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuerMetadata;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuerMetadataAssessment;

final class OpenId4VciIssuerMetadataAndCredentialConfigurationDiscoveryTest extends TestCase
{
    public function testCredentialConfigurationKeepsFormatBindingAndProofCapabilitiesExplicit(): void
    {
        $configuration = new CredentialConfiguration(
            'identity-credential',
            'vc+sd-jwt',
            ['jwk'],
            ['jwt'],
            ['scope' => 'identity']
        );

        self::assertSame(
            'identity-credential',
            $configuration->configurationId()
        );
        self::assertSame('vc+sd-jwt', $configuration->format());
        self::assertSame(
            ['jwk'],
            $configuration->cryptographicBindingMethods()
        );
        self::assertSame(['jwt'], $configuration->proofTypes());
        self::assertSame('identity', $configuration->metadata()['scope']);
    }

    public function testIssuerMetadataKeepsEndpointsAndConfigurationsExplicit(): void
    {
        $configuration = $this->configuration();

        $metadata = new CredentialIssuerMetadata(
            'https://issuer.example.test',
            'https://issuer.example.test/credential',
            ['identity-credential' => $configuration],
            'https://issuer.example.test/batch',
            'https://issuer.example.test/deferred',
            'https://issuer.example.test/notification'
        );

        self::assertSame(
            'https://issuer.example.test',
            $metadata->credentialIssuer()
        );
        self::assertSame(
            'https://issuer.example.test/credential',
            $metadata->credentialEndpoint()
        );
        self::assertArrayHasKey(
            'identity-credential',
            $metadata->credentialConfigurations()
        );
        self::assertSame(
            'https://issuer.example.test/batch',
            $metadata->batchCredentialEndpoint()
        );
        self::assertSame(
            'https://issuer.example.test/deferred',
            $metadata->deferredCredentialEndpoint()
        );
        self::assertSame(
            'https://issuer.example.test/notification',
            $metadata->notificationEndpoint()
        );
    }

    public function testMetadataAssessmentSeparatesViolationsAndWarnings(): void
    {
        $assessment = new CredentialIssuerMetadataAssessment(
            false,
            ['credential_endpoint_missing'],
            ['notification_endpoint_not_configured']
        );

        self::assertFalse($assessment->valid());
        self::assertSame(
            ['credential_endpoint_missing'],
            $assessment->violations()
        );
        self::assertSame(
            ['notification_endpoint_not_configured'],
            $assessment->warnings()
        );
    }

    public function testMetadataProviderResolverAndValidatorAreTyped(): void
    {
        $provider = new \ReflectionMethod(
            CredentialIssuerMetadataProviderInterface::class,
            'metadata'
        );
        $resolver = new \ReflectionMethod(
            CredentialIssuerMetadataResolverInterface::class,
            'resolve'
        );
        $validator = new \ReflectionMethod(
            CredentialIssuerMetadataValidatorInterface::class,
            'validate'
        );

        self::assertSame(
            CredentialIssuerMetadata::class,
            (string) $provider->getReturnType()
        );
        self::assertSame(
            CredentialIssuerMetadata::class,
            (string) $resolver->getReturnType()
        );
        self::assertSame(
            CredentialIssuerMetadataAssessment::class,
            (string) $validator->getReturnType()
        );
    }

    public function testConfigurationResolverReturnsTypedConfiguration(): void
    {
        $method = new \ReflectionMethod(
            CredentialConfigurationResolverInterface::class,
            'resolve'
        );

        self::assertSame(
            CredentialConfiguration::class,
            (string) $method->getReturnType()
        );
    }

    public function testDiscoveryLayerRemainsHttpCacheAndStorageNeutral(): void
    {
        foreach ([
            CredentialIssuerMetadataProviderInterface::class,
            CredentialIssuerMetadataResolverInterface::class,
            CredentialIssuerMetadataValidatorInterface::class,
            CredentialConfigurationResolverInterface::class,
            CredentialIssuerMetadata::class,
            CredentialConfiguration::class,
            CredentialIssuerMetadataAssessment::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('Symfony', $source);
            self::assertStringNotContainsString('Laravel', $source);
        }
    }

    private function configuration(): CredentialConfiguration
    {
        return new CredentialConfiguration(
            'identity-credential',
            'vc+sd-jwt',
            ['jwk'],
            ['jwt']
        );
    }
}
