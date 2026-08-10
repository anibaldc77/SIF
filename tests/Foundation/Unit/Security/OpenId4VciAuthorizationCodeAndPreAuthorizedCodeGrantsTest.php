<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\AuthorizationCodeIssuanceGrantValidatorInterface;
use Sif\Foundation\Security\Contracts\CredentialIssuanceGrantResolverInterface;
use Sif\Foundation\Security\Contracts\PreAuthorizedCodeIssuanceGrantValidatorInterface;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\AuthorizationCodeIssuanceGrant;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceGrantAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceGrantContext;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceGrantType;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\PreAuthorizedCodeIssuanceGrant;

final class OpenId4VciAuthorizationCodeAndPreAuthorizedCodeGrantsTest extends TestCase
{
    public function testGrantTypeSupportsAuthorizationAndPreAuthorizedCode(): void
    {
        self::assertSame(
            'authorization_code',
            (new CredentialIssuanceGrantType(
                CredentialIssuanceGrantType::AUTHORIZATION_CODE
            ))->value()
        );
        self::assertSame(
            'pre-authorized_code',
            (new CredentialIssuanceGrantType(
                CredentialIssuanceGrantType::PRE_AUTHORIZED_CODE
            ))->value()
        );
    }

    public function testAuthorizationCodeGrantKeepsOAuthBindingExplicit(): void
    {
        $grant = new AuthorizationCodeIssuanceGrant(
            'authorization-code-001',
            'wallet-client-001',
            'https://wallet.example.test/callback',
            'pkce-verifier-001'
        );

        self::assertSame(
            'authorization-code-001',
            $grant->authorizationCode()
        );
        self::assertSame('wallet-client-001', $grant->clientId());
        self::assertSame(
            'https://wallet.example.test/callback',
            $grant->redirectUri()
        );
        self::assertSame('pkce-verifier-001', $grant->codeVerifier());
    }

    public function testPreAuthorizedCodeGrantKeepsTxCodeExplicit(): void
    {
        $grant = new PreAuthorizedCodeIssuanceGrant(
            'pre-authorized-code-001',
            '482913'
        );

        self::assertSame(
            'pre-authorized-code-001',
            $grant->preAuthorizedCode()
        );
        self::assertSame('482913', $grant->txCode());
    }

    public function testGrantContextKeepsIssuerClientSubjectAndStateExplicit(): void
    {
        $context = new CredentialIssuanceGrantContext(
            'https://issuer.example.test',
            'wallet-client-001',
            'user-001',
            'issuer-state-001'
        );

        self::assertSame(
            'https://issuer.example.test',
            $context->credentialIssuer()
        );
        self::assertSame('wallet-client-001', $context->clientId());
        self::assertSame('user-001', $context->subjectId());
        self::assertSame('issuer-state-001', $context->issuerState());
    }

    public function testGrantAssessmentSeparatesViolationsAndWarnings(): void
    {
        $assessment = new CredentialIssuanceGrantAssessment(
            false,
            ['grant_expired'],
            ['tx_code_attempt_limit_near']
        );

        self::assertFalse($assessment->valid());
        self::assertSame(['grant_expired'], $assessment->violations());
        self::assertSame(
            ['tx_code_attempt_limit_near'],
            $assessment->warnings()
        );
    }

    public function testGrantContractsAreTyped(): void
    {
        $authorization = new \ReflectionMethod(
            AuthorizationCodeIssuanceGrantValidatorInterface::class,
            'validate'
        );
        $preAuthorized = new \ReflectionMethod(
            PreAuthorizedCodeIssuanceGrantValidatorInterface::class,
            'validate'
        );
        $resolver = new \ReflectionMethod(
            CredentialIssuanceGrantResolverInterface::class,
            'resolve'
        );

        self::assertSame(
            CredentialIssuanceGrantAssessment::class,
            (string) $authorization->getReturnType()
        );
        self::assertSame(
            CredentialIssuanceGrantAssessment::class,
            (string) $preAuthorized->getReturnType()
        );
        self::assertSame(
            CredentialIssuanceGrantType::class,
            (string) $resolver->getReturnType()
        );
    }

    public function testGrantLayerRemainsOAuthServerAndTransportNeutral(): void
    {
        foreach ([
            AuthorizationCodeIssuanceGrantValidatorInterface::class,
            PreAuthorizedCodeIssuanceGrantValidatorInterface::class,
            CredentialIssuanceGrantResolverInterface::class,
            CredentialIssuanceGrantType::class,
            AuthorizationCodeIssuanceGrant::class,
            PreAuthorizedCodeIssuanceGrant::class,
            CredentialIssuanceGrantContext::class,
            CredentialIssuanceGrantAssessment::class,
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
