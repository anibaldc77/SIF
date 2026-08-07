<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Exceptions\InvalidSamlResponseException;
use Sif\Foundation\Security\Saml\NativeSamlResponseParser;
use Sif\Foundation\Security\Saml\SamlEntityId;
use Sif\Foundation\Security\Saml\SamlRequestId;
use Sif\Foundation\Security\Saml\SamlResponseValidationContext;
use Sif\Foundation\Security\Saml\SamlResponseValidator;

final class SamlResponseParsingInResponseToCorrelationAndStatusValidationTest extends TestCase
{
    public function testParsesSuccessfulResponseEnvelope(): void
    {
        $response = (new NativeSamlResponseParser())->parse(
            $this->responseXml()
        );

        self::assertSame(
            '_response-000000000001',
            $response->id()->value()
        );
        self::assertSame(
            'https://idp.example/saml',
            $response->issuer()->value()
        );
        self::assertTrue(
            $response->statusCode()->successful()
        );
        self::assertSame(
            '_request-0000000000001',
            $response->inResponseTo()?->value()
        );
    }

    public function testValidatesIssuerDestinationAndInResponseTo(): void
    {
        $response = (new NativeSamlResponseParser())->parse(
            $this->responseXml()
        );

        $result = (new SamlResponseValidator())->validate(
            $response,
            new SamlResponseValidationContext(
                new SamlEntityId(
                    'https://idp.example/saml'
                ),
                'https://sp.example/saml/acs',
                new SamlRequestId(
                    '_request-0000000000001'
                )
            )
        );

        self::assertTrue($result->valid());
        self::assertSame([], $result->violations());
    }

    public function testRejectsNonSuccessStatusSemantically(): void
    {
        $response = (new NativeSamlResponseParser())->parse(
            str_replace(
                'urn:oasis:names:tc:SAML:2.0:status:Success',
                'urn:oasis:names:tc:SAML:2.0:status:Responder',
                $this->responseXml()
            )
        );

        $result = (new SamlResponseValidator())->validate(
            $response,
            $this->context()
        );

        self::assertFalse($result->valid());
        self::assertContains(
            'status_not_success',
            $result->violations()
        );
    }

    public function testDetectsCorrelationAndDestinationMismatch(): void
    {
        $response = (new NativeSamlResponseParser())->parse(
            $this->responseXml()
        );

        $result = (new SamlResponseValidator())->validate(
            $response,
            new SamlResponseValidationContext(
                new SamlEntityId(
                    'https://idp.example/saml'
                ),
                'https://sp.example/wrong-acs',
                new SamlRequestId(
                    '_request-different-000001'
                )
            )
        );

        self::assertFalse($result->valid());
        self::assertContains(
            'destination_mismatch',
            $result->violations()
        );
        self::assertContains(
            'in_response_to_mismatch',
            $result->violations()
        );
    }

    public function testRejectsMalformedResponseXml(): void
    {
        $this->expectException(
            InvalidSamlResponseException::class
        );

        (new NativeSamlResponseParser())->parse(
            '<samlp:Response'
        );
    }

    public function testResponseLayerDoesNotVerifySignaturesYet(): void
    {
        foreach ([
            NativeSamlResponseParser::class,
            SamlResponseValidator::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringContainsString(
                $class === NativeSamlResponseParser::class
                    ? 'LIBXML_NONET'
                    : 'final readonly class',
                $source
            );
            self::assertStringNotContainsString(
                'openssl_verify',
                strtolower($source)
            );
            self::assertStringNotContainsString(
                'xmlseclibs',
                strtolower($source)
            );
            self::assertStringNotContainsString(
                'Keycloak',
                $source
            );
        }
    }

    private function context(): SamlResponseValidationContext
    {
        return new SamlResponseValidationContext(
            new SamlEntityId(
                'https://idp.example/saml'
            ),
            'https://sp.example/saml/acs',
            new SamlRequestId(
                '_request-0000000000001'
            )
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
    IssueInstant="2026-08-07T22:30:00Z"
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
}
