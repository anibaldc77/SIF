<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OpenId4VcHaipAuthorizationConformancePolicyInterface;
use Sif\Foundation\Security\Contracts\OpenId4VcHaipAuthorizationRequirementsProviderInterface;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\DefaultOpenId4VcHaipAuthorizationConformancePolicy;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipAuthorizationAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipAuthorizationContext;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipAuthorizationRequirements;

final class OpenId4VcHaipAuthorizationParPkceAndDPoPProfileTest extends TestCase
{
    private function conformantContext(): OpenId4VcHaipAuthorizationContext
    {
        return new OpenId4VcHaipAuthorizationContext(
            true,
            true,
            true,
            'S256',
            true,
            true,
            true,
            true
        );
    }

    public function testRequirementsMakeHaipIssuanceSecurityProfileExplicit(): void
    {
        $requirements = new OpenId4VcHaipAuthorizationRequirements();

        self::assertTrue($requirements->authorizationCodeRequired());
        self::assertTrue($requirements->pkceRequired());
        self::assertSame('S256', $requirements->pkceCodeChallengeMethod());
        self::assertTrue(
            $requirements->parRequiredWhenAuthorizationEndpointUsed()
        );
        self::assertTrue($requirements->authorizationResponseIssuerRequired());
        self::assertTrue($requirements->dpopRequired());
        self::assertTrue($requirements->dpopNonceSupportRequired());
        self::assertTrue($requirements->scopeCredentialTypeBindingRequired());
        self::assertCount(8, $requirements->toArray());
    }

    public function testConformantAuthorizationContextIsAccepted(): void
    {
        $assessment = (new DefaultOpenId4VcHaipAuthorizationConformancePolicy())
            ->assess($this->conformantContext());

        self::assertTrue($assessment->conformant());
        self::assertSame([], $assessment->violations());
    }

    public function testAuthorizationCodeIsMandatory(): void
    {
        $context = new OpenId4VcHaipAuthorizationContext(
            false, true, true, 'S256', true, true, true, true
        );

        $assessment = (new DefaultOpenId4VcHaipAuthorizationConformancePolicy())
            ->assess($context);

        self::assertFalse($assessment->conformant());
        self::assertContains(
            'authorization_code_required',
            $assessment->violations()
        );
    }

    public function testPkceMustUseS256(): void
    {
        $context = new OpenId4VcHaipAuthorizationContext(
            true, true, true, 'plain', true, true, true, true
        );

        $assessment = (new DefaultOpenId4VcHaipAuthorizationConformancePolicy())
            ->assess($context);

        self::assertFalse($assessment->conformant());
        self::assertContains('pkce_s256_required', $assessment->violations());
    }

    public function testParIsRequiredWhenAuthorizationEndpointIsUsed(): void
    {
        $context = new OpenId4VcHaipAuthorizationContext(
            true, true, false, 'S256', true, true, true, true
        );

        $assessment = (new DefaultOpenId4VcHaipAuthorizationConformancePolicy())
            ->assess($context);

        self::assertFalse($assessment->conformant());
        self::assertContains(
            'par_required_for_authorization_endpoint',
            $assessment->violations()
        );
    }

    public function testParIsNotRequiredWhenAuthorizationEndpointIsNotUsed(): void
    {
        $context = new OpenId4VcHaipAuthorizationContext(
            true, false, false, 'S256', true, true, true, true
        );

        $assessment = (new DefaultOpenId4VcHaipAuthorizationConformancePolicy())
            ->assess($context);

        self::assertTrue($assessment->conformant());
    }

    public function testAuthorizationResponseIssuerValidationIsMandatory(): void
    {
        $context = new OpenId4VcHaipAuthorizationContext(
            true, true, true, 'S256', false, true, true, true
        );

        $assessment = (new DefaultOpenId4VcHaipAuthorizationConformancePolicy())
            ->assess($context);

        self::assertContains(
            'authorization_response_issuer_required',
            $assessment->violations()
        );
    }

    public function testDpopAndDpopNonceSupportAreMandatory(): void
    {
        $context = new OpenId4VcHaipAuthorizationContext(
            true, true, true, 'S256', true, false, false, true
        );

        $assessment = (new DefaultOpenId4VcHaipAuthorizationConformancePolicy())
            ->assess($context);

        self::assertContains('dpop_required', $assessment->violations());
        self::assertContains(
            'dpop_nonce_support_required',
            $assessment->violations()
        );
    }

    public function testScopeMustMapToCredentialType(): void
    {
        $context = new OpenId4VcHaipAuthorizationContext(
            true, true, true, 'S256', true, true, true, false
        );

        $assessment = (new DefaultOpenId4VcHaipAuthorizationConformancePolicy())
            ->assess($context);

        self::assertContains(
            'scope_credential_type_binding_required',
            $assessment->violations()
        );
    }

    public function testAuthorizationContractsAreTypedAndSeparated(): void
    {
        $assess = new \ReflectionMethod(
            OpenId4VcHaipAuthorizationConformancePolicyInterface::class,
            'assess'
        );
        $requirements = new \ReflectionMethod(
            OpenId4VcHaipAuthorizationRequirementsProviderInterface::class,
            'requirements'
        );

        self::assertSame(
            OpenId4VcHaipAuthorizationAssessment::class,
            (string) $assess->getReturnType()
        );
        self::assertSame(
            OpenId4VcHaipAuthorizationRequirements::class,
            (string) $requirements->getReturnType()
        );
    }

    public function testArchitectureReusesFapiAndOpenId4VciSubsystems(): void
    {
        foreach ([
            \Sif\Foundation\Security\Contracts\FapiAuthorizationFlowPolicyInterface::class,
            \Sif\Foundation\Security\Contracts\FapiDPoPPolicyInterface::class,
            \Sif\Foundation\Security\Contracts\FapiClientPolicyInterface::class,
            \Sif\Foundation\Security\Contracts\AuthorizationCodeIssuanceGrantValidatorInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialIssuerMetadataResolverInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialIssuanceProductReadinessEvaluatorInterface::class,
            \Sif\Foundation\Security\Contracts\OpenId4VcHaipConformancePolicyInterface::class,
        ] as $contract) {
            self::assertTrue(interface_exists($contract), $contract);
        }
    }

    public function testAuthorizationProfileRemainsOAuthTransportAndCryptoNeutral(): void
    {
        foreach ([
            OpenId4VcHaipAuthorizationConformancePolicyInterface::class,
            OpenId4VcHaipAuthorizationRequirementsProviderInterface::class,
            OpenId4VcHaipAuthorizationRequirements::class,
            OpenId4VcHaipAuthorizationContext::class,
            OpenId4VcHaipAuthorizationAssessment::class,
            DefaultOpenId4VcHaipAuthorizationConformancePolicy::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('Firebase', $source);
        }
    }
}
