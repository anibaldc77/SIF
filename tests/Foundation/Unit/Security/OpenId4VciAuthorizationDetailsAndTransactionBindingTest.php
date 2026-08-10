<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\CredentialAuthorizationDetailValidatorInterface;
use Sif\Foundation\Security\Contracts\CredentialAuthorizationTransactionBinderInterface;
use Sif\Foundation\Security\Contracts\CredentialAuthorizationTransactionBindingRepositoryInterface;
use Sif\Foundation\Security\Contracts\CredentialAuthorizationTransactionPolicyInterface;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialAuthorizationAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialAuthorizationContext;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialAuthorizationDetail;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialAuthorizationTransactionBinding;

final class OpenId4VciAuthorizationDetailsAndTransactionBindingTest extends TestCase
{
    public function testAuthorizationDetailKeepsCredentialConfigurationsAndLocationsExplicit(): void
    {
        $detail = new CredentialAuthorizationDetail(
            'openid_credential',
            ['identity-credential'],
            ['https://issuer.example.test'],
            ['purpose' => 'employee-onboarding']
        );

        self::assertSame('openid_credential', $detail->type());
        self::assertSame(
            ['identity-credential'],
            $detail->credentialConfigurationIds()
        );
        self::assertSame(
            ['https://issuer.example.test'],
            $detail->locations()
        );
        self::assertSame(
            'employee-onboarding',
            $detail->metadata()['purpose']
        );
    }

    public function testAuthorizationContextKeepsIssuerClientSubjectAndRequestBindingExplicit(): void
    {
        $context = new CredentialAuthorizationContext(
            'https://issuer.example.test',
            'wallet-client-001',
            'user-001',
            'authorization-request-001'
        );

        self::assertSame(
            'https://issuer.example.test',
            $context->credentialIssuer()
        );
        self::assertSame('wallet-client-001', $context->clientId());
        self::assertSame('user-001', $context->subjectId());
        self::assertSame(
            'authorization-request-001',
            $context->authorizationRequestId()
        );
    }

    public function testAuthorizationAssessmentSeparatesViolationsAndWarnings(): void
    {
        $assessment = new CredentialAuthorizationAssessment(
            false,
            ['configuration_not_authorized'],
            ['location_restriction_not_evaluated']
        );

        self::assertFalse($assessment->authorized());
        self::assertSame(
            ['configuration_not_authorized'],
            $assessment->violations()
        );
        self::assertSame(
            ['location_restriction_not_evaluated'],
            $assessment->warnings()
        );
    }

    public function testTransactionBindingKeepsClientSubjectConfigurationAndRequestExplicit(): void
    {
        $binding = new CredentialAuthorizationTransactionBinding(
            'transaction-001',
            'wallet-client-001',
            'user-001',
            'identity-credential',
            'authorization-request-001'
        );

        self::assertSame('transaction-001', $binding->transactionId());
        self::assertSame('wallet-client-001', $binding->clientId());
        self::assertSame('user-001', $binding->subjectId());
        self::assertSame(
            'identity-credential',
            $binding->credentialConfigurationId()
        );
        self::assertSame(
            'authorization-request-001',
            $binding->authorizationRequestId()
        );
    }

    public function testAuthorizationContractsAreTyped(): void
    {
        $validator = new \ReflectionMethod(
            CredentialAuthorizationDetailValidatorInterface::class,
            'validate'
        );
        $binder = new \ReflectionMethod(
            CredentialAuthorizationTransactionBinderInterface::class,
            'bind'
        );

        self::assertSame(
            CredentialAuthorizationAssessment::class,
            (string) $validator->getReturnType()
        );
        self::assertSame(
            CredentialAuthorizationTransactionBinding::class,
            (string) $binder->getReturnType()
        );

        foreach ([
            CredentialAuthorizationTransactionBindingRepositoryInterface::class,
            CredentialAuthorizationTransactionPolicyInterface::class,
        ] as $contract) {
            self::assertTrue(
                (new \ReflectionClass($contract))->isInterface()
            );
        }
    }

    public function testAuthorizationBindingLayerRemainsOAuthAndStorageNeutral(): void
    {
        foreach ([
            CredentialAuthorizationDetailValidatorInterface::class,
            CredentialAuthorizationTransactionBinderInterface::class,
            CredentialAuthorizationTransactionBindingRepositoryInterface::class,
            CredentialAuthorizationTransactionPolicyInterface::class,
            CredentialAuthorizationDetail::class,
            CredentialAuthorizationContext::class,
            CredentialAuthorizationAssessment::class,
            CredentialAuthorizationTransactionBinding::class,
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
}
