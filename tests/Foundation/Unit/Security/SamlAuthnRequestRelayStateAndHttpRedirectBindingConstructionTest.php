<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\SamlRelayStateGeneratorInterface;
use Sif\Foundation\Security\Contracts\SamlRequestIdGeneratorInterface;
use Sif\Foundation\Security\Saml\SamlAuthnRequestXmlSerializer;
use Sif\Foundation\Security\Saml\SamlEndpoint;
use Sif\Foundation\Security\Saml\SamlEntityId;
use Sif\Foundation\Security\Saml\SamlHttpRedirectBindingEncoder;
use Sif\Foundation\Security\Saml\SamlIdentityProviderMetadata;
use Sif\Foundation\Security\Saml\SamlRelayState;
use Sif\Foundation\Security\Saml\SamlRequestId;
use Sif\Foundation\Security\Saml\SamlServiceProviderMetadata;
use Sif\Foundation\Security\Saml\SamlSpInitiatedLoginRequestFactory;

final class SamlAuthnRequestRelayStateAndHttpRedirectBindingConstructionTest extends TestCase
{
    public function testFactoryBuildsSpInitiatedRequestWithCorrelationValues(): void
    {
        $factory = new SamlSpInitiatedLoginRequestFactory(
            new StaticRequestIdGenerator(),
            new StaticRelayStateGenerator()
        );

        $result = $factory->create(
            $this->serviceProvider(),
            $this->identityProvider(),
            $this->now()
        );

        self::assertSame(
            '_request-0000000000001',
            $result['request']->id()->value()
        );
        self::assertSame(
            'relay-state-0001',
            $result['relay_state']->value()
        );
        self::assertSame(
            'https://idp.example/saml/sso',
            $result['request']->destination()
        );
    }

    public function testSerializerBuildsMinimalSaml20AuthnRequest(): void
    {
        $factory = new SamlSpInitiatedLoginRequestFactory(
            new StaticRequestIdGenerator(),
            new StaticRelayStateGenerator()
        );

        $result = $factory->create(
            $this->serviceProvider(),
            $this->identityProvider(),
            $this->now(),
            true
        );

        $xml = (new SamlAuthnRequestXmlSerializer())->serialize(
            $result['request']
        );

        self::assertStringContainsString(
            'Version="2.0"',
            $xml
        );
        self::assertStringContainsString(
            'ForceAuthn="true"',
            $xml
        );
        self::assertStringContainsString(
            'https://sp.example/saml',
            $xml
        );
        self::assertStringContainsString(
            'https://sp.example/saml/acs',
            $xml
        );
    }

    public function testRedirectBindingDeflatesAndBase64EncodesRequest(): void
    {
        $factory = new SamlSpInitiatedLoginRequestFactory(
            new StaticRequestIdGenerator(),
            new StaticRelayStateGenerator()
        );

        $result = $factory->create(
            $this->serviceProvider(),
            $this->identityProvider(),
            $this->now()
        );

        $redirect = (new SamlHttpRedirectBindingEncoder())->encode(
            $result['request'],
            $result['relay_state']
        );

        $decoded = base64_decode(
            $redirect->samlRequest(),
            true
        );

        self::assertNotFalse($decoded);

        $xml = gzinflate($decoded);

        self::assertIsString($xml);
        self::assertStringContainsString(
            '<samlp:AuthnRequest',
            $xml
        );
    }

    public function testRedirectQueryUsesRfc3986Encoding(): void
    {
        $factory = new SamlSpInitiatedLoginRequestFactory(
            new StaticRequestIdGenerator(),
            new StaticRelayStateGenerator()
        );

        $result = $factory->create(
            $this->serviceProvider(),
            $this->identityProvider(),
            $this->now()
        );

        $redirect = (new SamlHttpRedirectBindingEncoder())->encode(
            $result['request'],
            $result['relay_state']
        );

        self::assertStringContainsString(
            'SAMLRequest=',
            $redirect->queryString()
        );
        self::assertStringContainsString(
            'RelayState=relay-state-0001',
            $redirect->queryString()
        );
    }

    public function testRelayStateIsBounded(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );

        new SamlRelayState(
            str_repeat('a', 81)
        );
    }

    public function testConstructionLayerDoesNotPerformTransportOrSignatureVerification(): void
    {
        foreach ([
            SamlHttpRedirectBindingEncoder::class,
            SamlSpInitiatedLoginRequestFactory::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString(
                'curl_',
                strtolower($source)
            );
            self::assertStringNotContainsString(
                'openssl_verify',
                strtolower($source)
            );
            self::assertStringNotContainsString(
                'Keycloak',
                $source
            );
        }
    }

    private function serviceProvider(): SamlServiceProviderMetadata
    {
        return new SamlServiceProviderMetadata(
            new SamlEntityId(
                'https://sp.example/saml'
            ),
            new SamlEndpoint(
                'https://sp.example/saml/acs',
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST'
            )
        );
    }

    private function identityProvider(): SamlIdentityProviderMetadata
    {
        return new SamlIdentityProviderMetadata(
            new SamlEntityId(
                'https://idp.example/saml'
            ),
            [
                new SamlEndpoint(
                    'https://idp.example/saml/sso',
                    'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect'
                ),
            ]
        );
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable(
            '2026-08-07T22:00:00+00:00'
        );
    }
}

final class StaticRequestIdGenerator implements SamlRequestIdGeneratorInterface
{
    public function generate(): SamlRequestId
    {
        return new SamlRequestId(
            '_request-0000000000001'
        );
    }
}

final class StaticRelayStateGenerator implements SamlRelayStateGeneratorInterface
{
    public function generate(): SamlRelayState
    {
        return new SamlRelayState(
            'relay-state-0001'
        );
    }
}
