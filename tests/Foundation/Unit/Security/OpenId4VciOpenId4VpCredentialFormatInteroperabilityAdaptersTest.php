<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\CredentialFormatInteroperabilityPolicyInterface;
use Sif\Foundation\Security\Contracts\OpenId4VciCredentialFormatAdapterInterface;
use Sif\Foundation\Security\Contracts\OpenId4VpCredentialFormatAdapterInterface;
use Sif\Foundation\Security\VerifiableCredentials\Formats\CredentialFormatProfile;
use Sif\Foundation\Security\VerifiableCredentials\Formats\HighAssuranceCredentialFormat;
use Sif\Foundation\Security\VerifiableCredentials\Formats\Interoperability\CredentialFormatInteroperabilityAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Formats\Interoperability\CredentialFormatIssuanceProfile;
use Sif\Foundation\Security\VerifiableCredentials\Formats\Interoperability\CredentialFormatPresentationProfile;

final class OpenId4VciOpenId4VpCredentialFormatInteroperabilityAdaptersTest extends TestCase
{
    public function testIssuanceProfileKeepsConfigurationAndFormatExplicit(): void
    {
        $format = new CredentialFormatProfile(
            HighAssuranceCredentialFormat::SdJwtVc,
            'draft-ietf-oauth-sd-jwt-vc-17',
            ['ES256']
        );

        $profile = new CredentialFormatIssuanceProfile(
            'identity_credential',
            $format,
            ['proof_type' => 'jwt']
        );

        self::assertSame(
            'identity_credential',
            $profile->credentialConfigurationId()
        );
        self::assertSame($format, $profile->formatProfile());
        self::assertSame(
            'jwt',
            $profile->metadata()['proof_type']
        );
    }

    public function testPresentationProfileKeepsRequirementClaimsAndFormatExplicit(): void
    {
        $format = new CredentialFormatProfile(
            HighAssuranceCredentialFormat::IsoMdoc,
            'iso-18013-5',
            ['ES256']
        );

        $profile = new CredentialFormatPresentationProfile(
            'identity',
            $format,
            ['family_name', 'given_name'],
            ['response_mode' => 'direct_post']
        );

        self::assertSame('identity', $profile->requirementId());
        self::assertSame($format, $profile->formatProfile());
        self::assertSame(
            ['family_name', 'given_name'],
            $profile->requestedClaims()
        );
        self::assertSame(
            'direct_post',
            $profile->metadata()['response_mode']
        );
    }

    public function testAssessmentAggregatesFormatProfileAndClaimsCompatibility(): void
    {
        $assessment = new CredentialFormatInteroperabilityAssessment(
            true,
            true,
            true,
            true
        );

        self::assertTrue($assessment->compatible());
        self::assertTrue($assessment->formatSupported());
        self::assertTrue($assessment->profileSupported());
        self::assertTrue($assessment->claimsCompatible());
    }

    public function testInteroperabilityContractsAreTyped(): void
    {
        $issuance = new \ReflectionMethod(
            OpenId4VciCredentialFormatAdapterInterface::class,
            'assess'
        );
        $presentation = new \ReflectionMethod(
            OpenId4VpCredentialFormatAdapterInterface::class,
            'assess'
        );

        self::assertSame(
            CredentialFormatInteroperabilityAssessment::class,
            (string) $issuance->getReturnType()
        );
        self::assertSame(
            CredentialFormatInteroperabilityAssessment::class,
            (string) $presentation->getReturnType()
        );
        self::assertTrue(
            (new \ReflectionClass(
                CredentialFormatInteroperabilityPolicyInterface::class
            ))->isInterface()
        );
    }

    public function testFormatAdaptersRemainProtocolCoreAndCryptoNeutral(): void
    {
        foreach ([
            OpenId4VciCredentialFormatAdapterInterface::class,
            OpenId4VpCredentialFormatAdapterInterface::class,
            CredentialFormatInteroperabilityPolicyInterface::class,
            CredentialFormatIssuanceProfile::class,
            CredentialFormatPresentationProfile::class,
            CredentialFormatInteroperabilityAssessment::class,
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
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('Firebase', $source);
        }
    }

    public function testAdaptersDoNotDuplicateProtocolModels(): void
    {
        self::assertTrue(
            class_exists(
                \Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceRequest::class
            )
        );
        self::assertTrue(
            class_exists(
                \Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpAuthorizationRequest::class
            )
        );
        self::assertTrue(
            class_exists(
                \Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc\SdJwtVcCredentialPayload::class
            )
        );
        self::assertTrue(
            class_exists(
                \Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc\IsoMdocDocument::class
            )
        );
    }
}
