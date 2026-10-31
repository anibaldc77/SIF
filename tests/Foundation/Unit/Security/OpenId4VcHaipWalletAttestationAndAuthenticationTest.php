<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OpenId4VcHaipWalletAttestationVerifierInterface;
use Sif\Foundation\Security\Contracts\OpenId4VcHaipWalletAuthenticationPolicyInterface;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Wallet\DefaultOpenId4VcHaipWalletAuthenticationPolicy;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Wallet\OpenId4VcHaipWalletAttestation;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Wallet\OpenId4VcHaipWalletAuthenticationAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Wallet\OpenId4VcHaipWalletAuthenticationContext;

final class OpenId4VcHaipWalletAttestationAndAuthenticationTest extends TestCase
{
    private function attestation(): OpenId4VcHaipWalletAttestation
    {
        return new OpenId4VcHaipWalletAttestation(
            'https://wallet-provider.example.test',
            'wallet-implementation-family',
            'https://issuer.example.test',
            new DateTimeImmutable('2026-08-13T12:00:00Z'),
            new DateTimeImmutable('2026-08-13T13:00:00Z'),
            [
                'wallet_name' => 'Example Wallet',
                'wallet_link' => 'https://wallet.example.test',
            ]
        );
    }

    public function testWalletAttestationKeepsCoreClaimsExplicit(): void
    {
        $attestation = $this->attestation();

        self::assertSame(
            'https://wallet-provider.example.test',
            $attestation->issuer()
        );
        self::assertSame(
            'wallet-implementation-family',
            $attestation->subject()
        );
        self::assertSame(
            'https://issuer.example.test',
            $attestation->audience()
        );
        self::assertSame(
            'Example Wallet',
            $attestation->claims()['wallet_name']
        );
    }

    public function testConformantWalletAuthenticationIsAccepted(): void
    {
        $context = new OpenId4VcHaipWalletAuthenticationContext(
            $this->attestation(),
            'https://issuer.example.test',
            'wallet-implementation-family',
            new DateTimeImmutable('2026-08-13T12:30:00Z'),
            true
        );

        $assessment = (new DefaultOpenId4VcHaipWalletAuthenticationPolicy())
            ->assess($context);

        self::assertTrue($assessment->conformant());
        self::assertSame([], $assessment->violations());
    }

    public function testWrongAudienceIsRejected(): void
    {
        $context = new OpenId4VcHaipWalletAuthenticationContext(
            $this->attestation(),
            'https://other-issuer.example.test',
            'wallet-implementation-family',
            new DateTimeImmutable('2026-08-13T12:30:00Z'),
            true
        );

        $assessment = (new DefaultOpenId4VcHaipWalletAuthenticationPolicy())
            ->assess($context);

        self::assertContains(
            'wallet_attestation_wrong_audience',
            $assessment->violations()
        );
    }

    public function testClientIdMustMatchAttestationSubjectWhenPresent(): void
    {
        $context = new OpenId4VcHaipWalletAuthenticationContext(
            $this->attestation(),
            'https://issuer.example.test',
            'different-wallet-family',
            new DateTimeImmutable('2026-08-13T12:30:00Z'),
            true
        );

        $assessment = (new DefaultOpenId4VcHaipWalletAuthenticationPolicy())
            ->assess($context);

        self::assertContains(
            'wallet_attestation_client_id_subject_mismatch',
            $assessment->violations()
        );
    }

    public function testExpiredAttestationIsRejected(): void
    {
        $context = new OpenId4VcHaipWalletAuthenticationContext(
            $this->attestation(),
            'https://issuer.example.test',
            'wallet-implementation-family',
            new DateTimeImmutable('2026-08-13T13:00:01Z'),
            true
        );

        $assessment = (new DefaultOpenId4VcHaipWalletAuthenticationPolicy())
            ->assess($context);

        self::assertContains(
            'wallet_attestation_expired',
            $assessment->violations()
        );
    }

    public function testAttestationMustBeUsedAtClientAuthenticationEndpoint(): void
    {
        $context = new OpenId4VcHaipWalletAuthenticationContext(
            $this->attestation(),
            'https://issuer.example.test',
            'wallet-implementation-family',
            new DateTimeImmutable('2026-08-13T12:30:00Z'),
            false
        );

        $assessment = (new DefaultOpenId4VcHaipWalletAuthenticationPolicy())
            ->assess($context);

        self::assertContains(
            'wallet_attestation_client_authentication_required',
            $assessment->violations()
        );
    }

    public function testWalletAuthenticationContractsAreTypedAndSeparated(): void
    {
        $verify = new \ReflectionMethod(
            OpenId4VcHaipWalletAttestationVerifierInterface::class,
            'verify'
        );
        $assess = new \ReflectionMethod(
            OpenId4VcHaipWalletAuthenticationPolicyInterface::class,
            'assess'
        );

        self::assertSame('void', (string) $verify->getReturnType());
        self::assertSame(
            OpenId4VcHaipWalletAuthenticationAssessment::class,
            (string) $assess->getReturnType()
        );
    }

    public function testArchitecturePreservesI1I2AndExistingSecuritySubsystems(): void
    {
        foreach ([
            \Sif\Foundation\Security\Contracts\OpenId4VcHaipConformancePolicyInterface::class,
            \Sif\Foundation\Security\Contracts\OpenId4VcHaipAuthorizationConformancePolicyInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialIssuanceProductReadinessEvaluatorInterface::class,
            \Sif\Foundation\Security\Contracts\FapiProductReadinessEvaluatorInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustEnforcementPolicyInterface::class,
        ] as $contract) {
            self::assertTrue(interface_exists($contract), $contract);
        }
    }

    public function testWalletAttestationLayerRemainsJwtCryptoTransportAndStorageNeutral(): void
    {
        foreach ([
            OpenId4VcHaipWalletAttestationVerifierInterface::class,
            OpenId4VcHaipWalletAuthenticationPolicyInterface::class,
            OpenId4VcHaipWalletAttestation::class,
            OpenId4VcHaipWalletAuthenticationContext::class,
            OpenId4VcHaipWalletAuthenticationAssessment::class,
            DefaultOpenId4VcHaipWalletAuthenticationPolicy::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('Firebase', $source);
        }
    }
}
