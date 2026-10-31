<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OpenId4VcHaipConformancePolicyInterface;
use Sif\Foundation\Security\Contracts\OpenId4VcHaipInteroperabilityBridgeInterface;
use Sif\Foundation\Security\VerifiableCredentials\Haip\OpenId4VcHaipConformanceAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Haip\OpenId4VcHaipConformanceContext;
use Sif\Foundation\Security\VerifiableCredentials\Haip\OpenId4VcHaipCredentialFormat;
use Sif\Foundation\Security\VerifiableCredentials\Haip\OpenId4VcHaipFlow;
use Sif\Foundation\Security\VerifiableCredentials\Haip\OpenId4VcHaipProfile;

final class OpenId4VcHaipArchitectureAndConformanceBoundariesTest extends TestCase
{
    private function profile(): OpenId4VcHaipProfile
    {
        return new OpenId4VcHaipProfile(
            'haip-default',
            [
                OpenId4VcHaipFlow::Issuance,
                OpenId4VcHaipFlow::PresentationRedirect,
                OpenId4VcHaipFlow::PresentationDigitalCredentialsApi,
            ],
            [
                OpenId4VcHaipCredentialFormat::SdJwtVc,
                OpenId4VcHaipCredentialFormat::IsoMdoc,
            ]
        );
    }

    public function testHaipFlowsAreExplicit(): void
    {
        self::assertSame(
            'issuance',
            OpenId4VcHaipFlow::Issuance->value
        );
        self::assertSame(
            'presentation_redirect',
            OpenId4VcHaipFlow::PresentationRedirect->value
        );
        self::assertSame(
            'presentation_digital_credentials_api',
            OpenId4VcHaipFlow::PresentationDigitalCredentialsApi->value
        );
    }

    public function testHaipCredentialFormatsAreExplicit(): void
    {
        self::assertSame(
            'dc+sd-jwt',
            OpenId4VcHaipCredentialFormat::SdJwtVc->value
        );
        self::assertSame(
            'mso_mdoc',
            OpenId4VcHaipCredentialFormat::IsoMdoc->value
        );
    }

    public function testProfileKeepsEnabledFlowsAndFormatsExplicit(): void
    {
        $profile = $this->profile();

        self::assertSame('haip-default', $profile->name());
        self::assertCount(3, $profile->flows());
        self::assertCount(2, $profile->credentialFormats());
        self::assertTrue(
            $profile->supportsFlow(OpenId4VcHaipFlow::Issuance)
        );
        self::assertTrue(
            $profile->supportsCredentialFormat(
                OpenId4VcHaipCredentialFormat::SdJwtVc
            )
        );
    }

    public function testConformanceContextKeepsCapabilitiesAndControlsExplicit(): void
    {
        $context = new OpenId4VcHaipConformanceContext(
            $this->profile(),
            [
                'openid4vci',
                'openid4vp',
                'digital_credentials_api',
                'sd_jwt_vc',
                'iso_mdoc',
            ],
            [
                'fapi2',
                'pkce_s256',
                'par',
                'dpop',
            ]
        );

        self::assertTrue($context->hasCapability('openid4vci'));
        self::assertTrue($context->hasCapability('iso_mdoc'));
        self::assertTrue($context->hasSecurityControl('fapi2'));
        self::assertTrue($context->hasSecurityControl('dpop'));
    }

    public function testAssessmentRepresentsConformantAndBlockedStates(): void
    {
        $conformant = new OpenId4VcHaipConformanceAssessment(true);

        self::assertTrue($conformant->conformant());
        self::assertSame([], $conformant->violations());

        $blocked = new OpenId4VcHaipConformanceAssessment(
            false,
            ['dpop_missing'],
            ['wallet attestation policy requires configuration']
        );

        self::assertFalse($blocked->conformant());
        self::assertSame(['dpop_missing'], $blocked->violations());
        self::assertSame(
            ['wallet attestation policy requires configuration'],
            $blocked->warnings()
        );
    }

    public function testHaipContractsAreTypedAndSeparated(): void
    {
        $assess = new \ReflectionMethod(
            OpenId4VcHaipConformancePolicyInterface::class,
            'assess'
        );
        $map = new \ReflectionMethod(
            OpenId4VcHaipInteroperabilityBridgeInterface::class,
            'map'
        );

        self::assertSame(
            OpenId4VcHaipConformanceAssessment::class,
            (string) $assess->getReturnType()
        );
        self::assertSame(
            \Sif\Foundation\Security\VerifiableCredentials\HighAssuranceInteroperabilityContext::class,
            (string) $map->getReturnType()
        );
    }

    public function testArchitectureComposesExistingSecuritySubsystems(): void
    {
        foreach ([
            \Sif\Foundation\Security\Contracts\FapiProductReadinessEvaluatorInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialIssuanceProductReadinessEvaluatorInterface::class,
            \Sif\Foundation\Security\Contracts\OpenId4VpAuthorizationRequestValidatorInterface::class,
            \Sif\Foundation\Security\Contracts\SdJwtVcCredentialProcessorInterface::class,
            \Sif\Foundation\Security\Contracts\IsoMdocCredentialProcessorInterface::class,
            \Sif\Foundation\Security\Contracts\HighAssuranceCredentialStatusResolverInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustEnforcementPolicyInterface::class,
            \Sif\Foundation\Security\Contracts\HighAssuranceInteroperabilityPolicyInterface::class,
        ] as $contract) {
            self::assertTrue(interface_exists($contract), $contract);
        }
    }

    public function testHaipArchitectureDoesNotRequireOpenIdFederationForProtocolConformance(): void
    {
        self::assertTrue(
            interface_exists(
                \Sif\Foundation\Security\Contracts\OpenIdFederationProductReadinessEvaluatorInterface::class
            )
        );

        $reflection = new \ReflectionClass(
            OpenId4VcHaipConformanceContext::class
        );
        $source = file_get_contents(
            (string) $reflection->getFileName()
        );

        self::assertIsString($source);
        self::assertStringNotContainsString(
            'OpenIdFederation',
            $source
        );
    }

    public function testHaipLayerRemainsTransportCryptoAndStorageNeutral(): void
    {
        foreach ([
            OpenId4VcHaipConformancePolicyInterface::class,
            OpenId4VcHaipInteroperabilityBridgeInterface::class,
            OpenId4VcHaipProfile::class,
            OpenId4VcHaipConformanceContext::class,
            OpenId4VcHaipConformanceAssessment::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

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
