<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Exceptions\InvalidSamlAssertionException;
use Sif\Foundation\Security\Saml\NativeSamlAssertionParser;
use Sif\Foundation\Security\Saml\SamlAssertionValidationContext;
use Sif\Foundation\Security\Saml\SamlAssertionValidator;
use Sif\Foundation\Security\Saml\SamlEntityId;
use Sif\Foundation\Security\Saml\SamlRequestId;

final class SamlAssertionParsingConditionsAudienceAndSubjectConfirmationValidationTest extends TestCase
{
    public function testParsesAssertionSubjectConditionsAndAudience(): void
    {
        $assertion = (new NativeSamlAssertionParser())->parse(
            $this->assertionXml()
        );

        self::assertSame(
            '_assertion-000000000001',
            $assertion->id()->value()
        );
        self::assertSame(
            'user@example.com',
            $assertion->subject()->value()
        );
        self::assertSame(
            'https://sp.example/saml',
            $assertion->conditions()->audiences()[0]->value()
        );
        self::assertSame(
            'https://sp.example/saml/acs',
            $assertion->subjectConfirmationData()?->recipient()
        );
    }

    public function testValidatesConditionsAudienceAndSubjectConfirmation(): void
    {
        $assertion = (new NativeSamlAssertionParser())->parse(
            $this->assertionXml()
        );

        $result = (new SamlAssertionValidator())->validate(
            $assertion,
            $this->context()
        );

        self::assertTrue($result->valid());
        self::assertSame([], $result->violations());
    }

    public function testDetectsAudienceAndRecipientMismatch(): void
    {
        $assertion = (new NativeSamlAssertionParser())->parse(
            $this->assertionXml()
        );

        $result = (new SamlAssertionValidator())->validate(
            $assertion,
            new SamlAssertionValidationContext(
                new SamlEntityId(
                    'https://idp.example/saml'
                ),
                new SamlEntityId(
                    'https://other.example/saml'
                ),
                'https://sp.example/wrong-acs',
                new SamlRequestId(
                    '_request-0000000000001'
                ),
                new DateTimeImmutable(
                    '2026-08-07T22:35:00Z'
                ),
                new DateInterval('PT60S')
            )
        );

        self::assertFalse($result->valid());
        self::assertContains(
            'audience_mismatch',
            $result->violations()
        );
        self::assertContains(
            'subject_recipient_mismatch',
            $result->violations()
        );
    }

    public function testDetectsExpiredAssertionWithClockSkew(): void
    {
        $xml = str_replace(
            '2026-08-07T22:40:00Z',
            '2026-08-07T22:30:00Z',
            $this->assertionXml()
        );

        $assertion = (new NativeSamlAssertionParser())->parse($xml);

        $result = (new SamlAssertionValidator())->validate(
            $assertion,
            $this->context()
        );

        self::assertFalse($result->valid());
        self::assertContains(
            'conditions_expired',
            $result->violations()
        );
        self::assertContains(
            'subject_confirmation_expired',
            $result->violations()
        );
    }

    public function testRejectsMalformedAssertionXml(): void
    {
        $this->expectException(
            InvalidSamlAssertionException::class
        );

        (new NativeSamlAssertionParser())->parse(
            '<saml:Assertion'
        );
    }

    public function testAssertionLayerDoesNotVerifySignatureOrCreateSession(): void
    {
        foreach ([
            NativeSamlAssertionParser::class,
            SamlAssertionValidator::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString(
                'openssl_verify',
                strtolower($source)
            );
            self::assertStringNotContainsString(
                'session_start',
                strtolower($source)
            );
            self::assertStringNotContainsString(
                'Keycloak',
                $source
            );
        }
    }

    private function context(): SamlAssertionValidationContext
    {
        return new SamlAssertionValidationContext(
            new SamlEntityId(
                'https://idp.example/saml'
            ),
            new SamlEntityId(
                'https://sp.example/saml'
            ),
            'https://sp.example/saml/acs',
            new SamlRequestId(
                '_request-0000000000001'
            ),
            new DateTimeImmutable(
                '2026-08-07T22:35:00Z'
            ),
            new DateInterval('PT60S')
        );
    }

    private function assertionXml(): string
    {
        return <<<XML
<saml:Assertion
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="_assertion-000000000001"
    Version="2.0"
    IssueInstant="2026-08-07T22:30:00Z">
    <saml:Issuer>https://idp.example/saml</saml:Issuer>
    <saml:Subject>
        <saml:NameID
            Format="urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress">user@example.com</saml:NameID>
        <saml:SubjectConfirmation
            Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
            <saml:SubjectConfirmationData
                InResponseTo="_request-0000000000001"
                Recipient="https://sp.example/saml/acs"
                NotOnOrAfter="2026-08-07T22:40:00Z"/>
        </saml:SubjectConfirmation>
    </saml:Subject>
    <saml:Conditions
        NotBefore="2026-08-07T22:29:00Z"
        NotOnOrAfter="2026-08-07T22:40:00Z">
        <saml:AudienceRestriction>
            <saml:Audience>https://sp.example/saml</saml:Audience>
        </saml:AudienceRestriction>
    </saml:Conditions>
</saml:Assertion>
XML;
    }
}
