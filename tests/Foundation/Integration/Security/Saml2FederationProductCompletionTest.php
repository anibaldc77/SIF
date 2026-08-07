<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Integration\Security;

use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\SamlReplayStoreInterface;
use Sif\Foundation\Security\Contracts\SamlSessionEstablisherInterface;
use Sif\Foundation\Security\Contracts\SamlTrustStoreInterface;
use Sif\Foundation\Security\Contracts\SamlXmlSignatureVerifierInterface;
use Sif\Foundation\Security\Saml\DefaultSamlIdentityMapper;
use Sif\Foundation\Security\Saml\NativeSamlAssertionParser;
use Sif\Foundation\Security\Saml\NativeSamlResponseParser;
use Sif\Foundation\Security\Saml\SamlAssertionValidationContext;
use Sif\Foundation\Security\Saml\SamlAssertionValidator;
use Sif\Foundation\Security\Saml\SamlAuthenticatedIdentity;
use Sif\Foundation\Security\Saml\SamlAuthenticationCoordinator;
use Sif\Foundation\Security\Saml\SamlCertificateFingerprint;
use Sif\Foundation\Security\Saml\SamlEntityId;
use Sif\Foundation\Security\Saml\SamlReplayGuard;
use Sif\Foundation\Security\Saml\SamlRequestId;
use Sif\Foundation\Security\Saml\SamlResponseValidationContext;
use Sif\Foundation\Security\Saml\SamlResponseValidator;
use Sif\Foundation\Security\Saml\SamlSignaturePolicyValidator;
use Sif\Foundation\Security\Saml\SamlSignatureTrustValidator;
use Sif\Foundation\Security\Saml\SamlSignatureValidationContext;
use Sif\Foundation\Security\Saml\SamlSignedDocumentPolicy;
use Sif\Foundation\Security\Saml\SamlXmlSignatureVerificationResult;

final class Saml2FederationProductCompletionTest extends TestCase
{
    public function testValidatedSamlFlowMapsIdentityAndEstablishesSession(): void
    {
        $response = (new NativeSamlResponseParser())->parse(
            $this->responseXml()
        );

        $responseValidation = (new SamlResponseValidator())->validate(
            $response,
            new SamlResponseValidationContext(
                new SamlEntityId('https://idp.example/saml'),
                'https://sp.example/saml/acs',
                new SamlRequestId('_request-0000000000001')
            )
        );

        self::assertTrue($responseValidation->valid());

        $assertion = (new NativeSamlAssertionParser())->parse(
            $this->assertionXml()
        );

        $assertionValidation = (new SamlAssertionValidator())->validate(
            $assertion,
            new SamlAssertionValidationContext(
                new SamlEntityId('https://idp.example/saml'),
                new SamlEntityId('https://sp.example/saml'),
                'https://sp.example/saml/acs',
                new SamlRequestId('_request-0000000000001'),
                new DateTimeImmutable('2026-08-07T23:10:00Z'),
                new DateInterval('PT60S')
            )
        );

        self::assertTrue($assertionValidation->valid());

        $signatureValidation = (new SamlSignaturePolicyValidator(
            new SamlSignedDocumentPolicy(true, true),
            new SamlSignatureTrustValidator(
                new ProductTrustStore(),
                new ProductSignatureVerifier()
            )
        ))->validate(
            new SamlSignatureValidationContext(
                new SamlEntityId('https://idp.example/saml'),
                $this->fingerprint(),
                $this->responseXml(),
                $this->assertionXml()
            )
        );

        self::assertTrue($signatureValidation->verified());

        $replayStore = new ProductReplayStore();
        $guard = new SamlReplayGuard($replayStore);

        $guard->assertFresh(
            $response->id()->value(),
            new DateTimeImmutable('2026-08-07T23:30:00Z')
        );
        $guard->assertFresh(
            $assertion->id()->value(),
            new DateTimeImmutable('2026-08-07T23:30:00Z')
        );

        $session = new ProductSessionEstablisher();

        $result = (new SamlAuthenticationCoordinator(
            new DefaultSamlIdentityMapper(),
            $session
        ))->authenticate(
            $assertion,
            ['email' => 'user@example.com']
        );

        self::assertTrue($result->sessionEstablished());
        self::assertSame(
            'user@example.com',
            $result->identity()->subjectIdentifier()
        );
        self::assertSame(1, $session->calls());
    }

    public function testProductBoundaryRemainsProviderAndStorageNeutral(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/Saml';

        foreach (glob($directory . '/*.php') ?: [] as $file) {
            $source = file_get_contents($file);

            self::assertIsString($source);
            self::assertStringNotContainsString('Keycloak', $source);
            self::assertStringNotContainsString('OneLogin', $source);
            self::assertStringNotContainsString('SimpleSAML', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
        }
    }

    public function testProductDoesNotCreatePhpSessionsDirectly(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/Saml';

        foreach (glob($directory . '/*.php') ?: [] as $file) {
            $source = file_get_contents($file);

            self::assertIsString($source);
            self::assertStringNotContainsString(
                'session_start',
                strtolower($source)
            );
            self::assertStringNotContainsString(
                'setcookie',
                strtolower($source)
            );
        }
    }

    public function testProductDoesNotPerformRemoteTransportInternally(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/Saml';

        foreach (glob($directory . '/*.php') ?: [] as $file) {
            $source = file_get_contents($file);

            self::assertIsString($source);
            self::assertStringNotContainsString(
                'file_get_contents("http',
                strtolower($source)
            );
            self::assertStringNotContainsString(
                'curl_',
                strtolower($source)
            );
        }
    }

    public function testReplayStoreIsRequiredAtApplicationBoundary(): void
    {
        $store = new ProductReplayStore();
        $guard = new SamlReplayGuard($store);

        $expiresAt = new DateTimeImmutable(
            '2026-08-07T23:30:00Z'
        );

        $guard->assertFresh(
            '_response-product-0001',
            $expiresAt
        );

        self::assertTrue(
            $store->contains('_response-product-0001')
        );
    }

    public function testTrustAndSignatureRemainExplicitBeforeAuthentication(): void
    {
        $trust = new ProductTrustStore();
        $verifier = new ProductSignatureVerifier();

        $result = (new SamlSignatureTrustValidator(
            $trust,
            $verifier
        ))->validate(
            new SamlEntityId('https://idp.example/saml'),
            '<signed/>',
            $this->fingerprint()
        );

        self::assertTrue($result->verified());
    }

    private function fingerprint(): SamlCertificateFingerprint
    {
        return new SamlCertificateFingerprint(
            str_repeat('ab', 32)
        );
    }

    private function responseXml(): string
    {
        return <<<XML
<samlp:Response
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="_response-000000000001"
    Version="2.0"
    IssueInstant="2026-08-07T23:00:00Z"
    Destination="https://sp.example/saml/acs"
    InResponseTo="_request-0000000000001">
    <saml:Issuer>https://idp.example/saml</saml:Issuer>
    <samlp:Status>
        <samlp:StatusCode
            Value="urn:oasis:names:tc:SAML:2.0:status:Success"/>
    </samlp:Status>
</samlp:Response>
XML;
    }

    private function assertionXml(): string
    {
        return <<<XML
<saml:Assertion
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="_assertion-000000000001"
    Version="2.0"
    IssueInstant="2026-08-07T23:00:00Z">
    <saml:Issuer>https://idp.example/saml</saml:Issuer>
    <saml:Subject>
        <saml:NameID>user@example.com</saml:NameID>
        <saml:SubjectConfirmation
            Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
            <saml:SubjectConfirmationData
                InResponseTo="_request-0000000000001"
                Recipient="https://sp.example/saml/acs"
                NotOnOrAfter="2026-08-07T23:20:00Z"/>
        </saml:SubjectConfirmation>
    </saml:Subject>
    <saml:Conditions
        NotBefore="2026-08-07T22:59:00Z"
        NotOnOrAfter="2026-08-07T23:20:00Z">
        <saml:AudienceRestriction>
            <saml:Audience>https://sp.example/saml</saml:Audience>
        </saml:AudienceRestriction>
    </saml:Conditions>
</saml:Assertion>
XML;
    }
}

final class ProductReplayStore implements SamlReplayStoreInterface
{
    /** @var array<string,DateTimeImmutable> */
    private array $ids = [];

    public function contains(string $identifier): bool
    {
        return isset($this->ids[$identifier]);
    }

    public function remember(
        string $identifier,
        DateTimeImmutable $expiresAt
    ): void {
        $this->ids[$identifier] = $expiresAt;
    }
}

final class ProductSessionEstablisher implements SamlSessionEstablisherInterface
{
    private int $calls = 0;

    public function establish(
        SamlAuthenticatedIdentity $identity
    ): void {
        $this->calls++;
    }

    public function calls(): int
    {
        return $this->calls;
    }
}

final class ProductTrustStore implements SamlTrustStoreInterface
{
    public function trusts(
        SamlEntityId $entityId,
        SamlCertificateFingerprint $fingerprint
    ): bool {
        return $entityId->value() === 'https://idp.example/saml'
            && $fingerprint->sha256() === str_repeat('ab', 32);
    }
}

final class ProductSignatureVerifier implements SamlXmlSignatureVerifierInterface
{
    public function verify(
        string $xml,
        SamlCertificateFingerprint $expectedFingerprint
    ): SamlXmlSignatureVerificationResult {
        return $xml !== ''
            ? SamlXmlSignatureVerificationResult::success()
            : SamlXmlSignatureVerificationResult::failed([
                'signature_invalid',
            ]);
    }
}
