<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Exceptions\InvalidSamlMetadataException;
use Sif\Foundation\Security\Saml\NativeSamlMetadataParser;
use Sif\Foundation\Security\Saml\SamlX509Certificate;

final class SamlMetadataXmlParsingStructuralValidationAndCertificateExtractionTest extends TestCase
{
    public function testParsesIdentityProviderMetadata(): void
    {
        $metadata = (new NativeSamlMetadataParser())->parse(
            $this->validMetadata()
        );

        self::assertSame(
            'https://idp.example/saml',
            $metadata->entityId()->value()
        );
        self::assertSame(
            'https://idp.example/saml/sso',
            $metadata->singleSignOnServices()[0]->location()
        );
        self::assertSame(
            'https://idp.example/saml/logout',
            $metadata->singleLogoutServices()[0]->location()
        );
    }

    public function testExtractsSigningCertificateFingerprint(): void
    {
        $metadata = (new NativeSamlMetadataParser())->parse(
            $this->validMetadata()
        );

        $certificate = new SamlX509Certificate(
            base64_encode('certificate-der-material')
        );

        self::assertCount(
            1,
            $metadata->signingCertificateFingerprints()
        );
        self::assertSame(
            $certificate->fingerprint()->sha256(),
            $metadata->signingCertificateFingerprints()[0]->sha256()
        );
    }

    public function testRejectsMalformedXml(): void
    {
        $this->expectException(
            InvalidSamlMetadataException::class
        );

        (new NativeSamlMetadataParser())->parse(
            '<EntityDescriptor>'
        );
    }

    public function testRejectsWrongRootElement(): void
    {
        $this->expectException(
            InvalidSamlMetadataException::class
        );

        (new NativeSamlMetadataParser())->parse(
            '<root xmlns="urn:oasis:names:tc:SAML:2.0:metadata"/>'
        );
    }

    public function testRejectsMetadataWithoutSsoEndpoint(): void
    {
        $this->expectException(
            InvalidSamlMetadataException::class
        );

        (new NativeSamlMetadataParser())->parse(
            <<<XML
<md:EntityDescriptor
    xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata"
    entityID="https://idp.example/saml">
    <md:IDPSSODescriptor/>
</md:EntityDescriptor>
XML
        );
    }

    public function testParserDisablesNetworkAndDoesNotPerformTransport(): void
    {
        $reflection = new \ReflectionClass(
            NativeSamlMetadataParser::class
        );
        $source = file_get_contents(
            (string) $reflection->getFileName()
        );

        self::assertIsString($source);
        self::assertStringContainsString('LIBXML_NONET', $source);
        self::assertStringNotContainsString('curl_', strtolower($source));
        self::assertStringNotContainsString('file_get_contents("http', strtolower($source));
        self::assertStringNotContainsString('Keycloak', $source);
    }

    private function validMetadata(): string
    {
        $certificate = base64_encode(
            'certificate-der-material'
        );

        return <<<XML
<md:EntityDescriptor
    xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata"
    xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
    entityID="https://idp.example/saml">
    <md:IDPSSODescriptor>
        <md:KeyDescriptor use="signing">
            <ds:KeyInfo>
                <ds:X509Data>
                    <ds:X509Certificate>{$certificate}</ds:X509Certificate>
                </ds:X509Data>
            </ds:KeyInfo>
        </md:KeyDescriptor>
        <md:SingleSignOnService
            Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
            Location="https://idp.example/saml/sso"/>
        <md:SingleLogoutService
            Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
            Location="https://idp.example/saml/logout"/>
    </md:IDPSSODescriptor>
</md:EntityDescriptor>
XML;
    }
}
