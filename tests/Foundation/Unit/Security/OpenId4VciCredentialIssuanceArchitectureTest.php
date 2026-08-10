<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\CredentialConfigurationProviderInterface;
use Sif\Foundation\Security\Contracts\CredentialIssuanceProofValidatorInterface;
use Sif\Foundation\Security\Contracts\CredentialIssuanceServiceInterface;
use Sif\Foundation\Security\Contracts\CredentialOfferParserInterface;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceContext;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceRequest;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceResponse;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialOffer;

final class OpenId4VciCredentialIssuanceArchitectureTest extends TestCase
{
    public function testCredentialOfferKeepsIssuerConfigurationsAndGrantsExplicit(): void
    {
        $offer = new CredentialOffer(
            'https://issuer.example.test',
            ['identity-credential'],
            ['authorization_code', 'pre-authorized_code'],
            'issuer-state-001'
        );

        self::assertSame(
            'https://issuer.example.test',
            $offer->credentialIssuer()
        );
        self::assertSame(
            ['identity-credential'],
            $offer->credentialConfigurationIds()
        );
        self::assertContains(
            'authorization_code',
            $offer->grantTypes()
        );
        self::assertSame('issuer-state-001', $offer->issuerState());
    }

    public function testIssuanceRequestKeepsConfigurationProofAndIdentifierExplicit(): void
    {
        $request = new CredentialIssuanceRequest(
            'identity-credential',
            ['proof_type' => 'jwt'],
            'credential-001'
        );

        self::assertSame(
            'identity-credential',
            $request->credentialConfigurationId()
        );
        self::assertSame('jwt', $request->proof()['proof_type']);
        self::assertSame(
            'credential-001',
            $request->credentialIdentifier()
        );
    }

    public function testIssuanceResponseCanRepresentImmediateCredential(): void
    {
        $response = new CredentialIssuanceResponse(
            'serialized-credential',
            null,
            'nonce-001'
        );

        self::assertSame(
            'serialized-credential',
            $response->credential()
        );
        self::assertFalse($response->deferred());
        self::assertSame('nonce-001', $response->cNonce());
    }

    public function testIssuanceResponseCanRepresentDeferredIssuance(): void
    {
        $response = new CredentialIssuanceResponse(
            null,
            'transaction-001'
        );

        self::assertNull($response->credential());
        self::assertSame(
            'transaction-001',
            $response->transactionId()
        );
        self::assertTrue($response->deferred());
    }

    public function testIssuanceContextKeepsIssuerSubjectAndClientExplicit(): void
    {
        $context = new CredentialIssuanceContext(
            'https://issuer.example.test',
            'user-001',
            'wallet-client-001',
            'access-token-ref-001'
        );

        self::assertSame(
            'https://issuer.example.test',
            $context->issuer()
        );
        self::assertSame('user-001', $context->subjectId());
        self::assertSame('wallet-client-001', $context->clientId());
        self::assertSame(
            'access-token-ref-001',
            $context->accessTokenReference()
        );
    }

    public function testIssuanceContractsAreTyped(): void
    {
        $parse = new \ReflectionMethod(
            CredentialOfferParserInterface::class,
            'parse'
        );
        $issue = new \ReflectionMethod(
            CredentialIssuanceServiceInterface::class,
            'issue'
        );

        self::assertSame(
            CredentialOffer::class,
            (string) $parse->getReturnType()
        );
        self::assertSame(
            CredentialIssuanceResponse::class,
            (string) $issue->getReturnType()
        );

        foreach ([
            CredentialIssuanceProofValidatorInterface::class,
            CredentialConfigurationProviderInterface::class,
        ] as $contract) {
            self::assertTrue(
                (new \ReflectionClass($contract))->isInterface()
            );
        }
    }

    public function testIssuanceLayerRemainsTransportWalletAndFormatNeutral(): void
    {
        foreach ([
            CredentialOfferParserInterface::class,
            CredentialIssuanceServiceInterface::class,
            CredentialIssuanceProofValidatorInterface::class,
            CredentialConfigurationProviderInterface::class,
            CredentialOffer::class,
            CredentialIssuanceRequest::class,
            CredentialIssuanceResponse::class,
            CredentialIssuanceContext::class,
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
