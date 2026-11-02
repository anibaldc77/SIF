<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Sif\Adapters\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipConfiguredTrustPolicyProvider;
use Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyAttestationTrustPolicyProviderInterface;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationTrustPolicy;

final class OpenId4VcHaipConfiguredTrustPolicyProviderTest extends TestCase
{
    public function testEmptyConfigurationDeniesEveryIssuer(): void
    {
        $provider = new OpenId4VcHaipConfiguredTrustPolicyProvider();

        self::assertNull(
            $provider->forCredentialIssuer('https://issuer.example')
        );
    }

    public function testReturnsOnlyTheExactConfiguredPolicy(): void
    {
        $policy = self::policy('https://issuer.example');
        $provider = new OpenId4VcHaipConfiguredTrustPolicyProvider(
            [$policy]
        );

        self::assertSame(
            $policy,
            $provider->forCredentialIssuer('https://issuer.example')
        );
    }

    public function testUnknownIssuerHasNoDefaultFallback(): void
    {
        $provider = new OpenId4VcHaipConfiguredTrustPolicyProvider([
            self::policy('https://issuer.example'),
        ]);

        self::assertNull(
            $provider->forCredentialIssuer('https://unknown.example')
        );
    }

    public function testIssuerLookupIsCaseSensitive(): void
    {
        $provider = new OpenId4VcHaipConfiguredTrustPolicyProvider([
            self::policy('https://issuer.example/Alpha'),
        ]);

        self::assertNull(
            $provider->forCredentialIssuer('https://issuer.example/alpha')
        );

        self::assertNotNull(
            $provider->forCredentialIssuer('https://issuer.example/Alpha')
        );
    }

    public function testMultipleIssuerPoliciesRemainIsolated(): void
    {
        $first = self::policy('https://issuer-a.example');
        $second = self::policy('https://issuer-b.example');

        $provider = new OpenId4VcHaipConfiguredTrustPolicyProvider([
            $first,
            $second,
        ]);

        self::assertSame(
            $first,
            $provider->forCredentialIssuer('https://issuer-a.example')
        );

        self::assertSame(
            $second,
            $provider->forCredentialIssuer('https://issuer-b.example')
        );
    }

    public function testDuplicateCredentialIssuerIsRejected(): void
    {
        $policy = self::policy('https://issuer.example');

        $this->expectException(InvalidArgumentException::class);

        new OpenId4VcHaipConfiguredTrustPolicyProvider([
            $policy,
            $policy,
        ]);
    }

    public function testWalletProviderIdentityCannotSelectIssuerPolicy(): void
    {
        $provider = new OpenId4VcHaipConfiguredTrustPolicyProvider([
            self::policy('https://credential-issuer.example'),
        ]);

        self::assertNull(
            $provider->forCredentialIssuer('https://wallet-provider.example')
        );
    }

    public function testInvalidLookupIdentifiersAreDenied(): void
    {
        $provider = new OpenId4VcHaipConfiguredTrustPolicyProvider([
            self::policy('https://issuer.example'),
        ]);

        self::assertNull($provider->forCredentialIssuer(''));

        self::assertNull(
            $provider->forCredentialIssuer(' https://issuer.example')
        );

        self::assertNull(
            $provider->forCredentialIssuer('https://issuer.example ')
        );
    }

    public function testProviderImplementsContractAndIsReadonly(): void
    {
        $provider = new OpenId4VcHaipConfiguredTrustPolicyProvider();

        self::assertInstanceOf(
            OpenId4VcHaipKeyAttestationTrustPolicyProviderInterface::class,
            $provider
        );

        self::assertTrue(
            (new ReflectionClass(
                OpenId4VcHaipConfiguredTrustPolicyProvider::class
            ))->isReadOnly()
        );
    }

    private static function policy(
        string $issuerIdentifier
    ): OpenId4VcHaipKeyAttestationTrustPolicy {
        // Structural test fixture only, not a real trusted CA.
        $anchor = "-----BEGIN CERTIFICATE-----\n"
            . base64_encode('test-only-anchor')
            . "\n-----END CERTIFICATE-----\n";

        return new OpenId4VcHaipKeyAttestationTrustPolicy(
            $issuerIdentifier,
            [$anchor],
            ['https://wallet-provider.example'],
            ['ES256']
        );
    }
}
